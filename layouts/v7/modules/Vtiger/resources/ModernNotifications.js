(function () {
  "use strict";

  var ModernNotifications = {
    previousIds: [],
    lastRenderedNotificationId: 0,
    intervalId: null,
    sound: null,
    initialized: false,
    currentTab: "unread",

    init: function () {
      if (this.initialized) {
        return;
      }

      // Initialize sound with proper path
      this.initSound();

      // Ensure we start on unread tab
      this.currentTab = "unread";
      this.loadUnreadNotifications();

      // Poll for new unread notifications every 3 seconds
      this.intervalId = setInterval(function () {
        // Always update badge count, but only render if on unread tab
        if (ModernNotifications.currentTab === "unread") {
          ModernNotifications.loadUnreadNotifications();
        } else {
          // Still check for new notifications to update badge
          ModernNotifications.checkUnreadCountOnly();
        }
      }, 3000);

      this.setupTabHandlers();

      this.initialized = true;
    },

    checkUnreadCountOnly: function () {
      var self = this;
      var url = "index.php?module=Vtiger&action=Notifications&type=unread";

      jQuery.ajax({
        url: url,
        type: "GET",
        dataType: "json",
        cache: false, // Prevent Chrome cache issues
        xhrFields: {
          withCredentials: true // Ensure cookies are sent (required for Chrome)
        },
        success: function (response) {
          if (response && response.success) {
            // Only update badge, don't render list
            self.updateNotificationBadge(response.count || 0);
            // Check for new notifications to play sound and shake bell
            if (response.list && response.list.length > 0) {
              self.checkForNewNotifications(response.list);
            }
          }
        },
        error: function (xhr, status, error) {
          // Log error for debugging
          console.warn("[ModernNotifications] Error loading unread count:", status, error);
        },
      });
    },

    initSound: function () {
      try {
        // Get base URL - try multiple methods
        var baseUrl = "";
        if (typeof _META !== "undefined" && _META.notifier) {
          // Try to extract base URL from Vtiger's _META
          var notifierUrl = _META.notifier;
          baseUrl = notifierUrl.substring(0, notifierUrl.lastIndexOf("/"));
        } else {
          // Fallback: construct from current location
          var pathParts = window.location.pathname.split("/");
          pathParts = pathParts.filter(function (part) {
            return part && part !== "index.php";
          });
          baseUrl = window.location.origin;
          if (pathParts.length > 0 && pathParts[0] !== "") {
            baseUrl += "/" + pathParts[0];
          }
        }

        // Remove trailing slash
        if (baseUrl.endsWith("/")) {
          baseUrl = baseUrl.slice(0, -1);
        }

        var soundPath =
          baseUrl +
          "/layouts/v7/modules/Vtiger/resources/sounds/notification.mp3";

        this.sound = new Audio(soundPath);
        this.sound.volume = 0.7;
        this.sound.preload = "auto";

        // Preload sound on user interaction (required by browsers for autoplay)
        var self = this;
        var preloadSound = function () {
          if (self.sound) {
            self.sound.load().catch(function (e) {
              console.warn("[ModernNotifications] Sound preload failed:", e);
            });
          }
          document.removeEventListener("click", preloadSound);
          document.removeEventListener("touchstart", preloadSound);
          document.removeEventListener("keydown", preloadSound);
        };
        document.addEventListener("click", preloadSound, { once: true });
        document.addEventListener("touchstart", preloadSound, { once: true });
        document.addEventListener("keydown", preloadSound, { once: true });
      } catch (e) {
        console.warn("[ModernNotifications] Sound initialization failed:", e);
      }
    },

    setupTabHandlers: function () {
      var self = this;
      jQuery("#modern-notifications-tab-unread-link").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        self.switchTab("unread");
      });

      jQuery("#modern-notifications-tab-read-link").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        self.switchTab("read");
      });
    },

    switchTab: function (tab) {
      if (this.currentTab === tab) {
        return;
      }

      // CRITICAL: Set currentTab BEFORE any async operations
      this.currentTab = tab;

      var unreadTab = jQuery("#modern-notifications-tab-unread");
      var readTab = jQuery("#modern-notifications-tab-read");
      var unreadEmpty = jQuery("#modern-notifications-empty-unread");
      var readEmpty = jQuery("#modern-notifications-empty-read");
      var itemsContainer = jQuery("#modern-notifications-items");
      var actionsContainer = jQuery("#modern-notifications-actions");

      // Always clear items container first
      itemsContainer.empty();
      itemsContainer.hide();
      actionsContainer.hide();

      if (tab === "unread") {
        unreadTab.addClass("active");
        readTab.removeClass("active");
        // Hide read empty, show unread empty (will be hidden if we have data)
        readEmpty.hide();
        unreadEmpty.show();
        // CRITICAL: Load unread notifications AFTER setting currentTab
        var self = this;
        // Use setTimeout to ensure currentTab is set before AJAX callback
        setTimeout(function () {
          // Double-check we're still on unread tab before loading
          if (self.currentTab === "unread") {
            self.loadUnreadNotifications();
          }
        }, 0);
      } else {
        readTab.addClass("active");
        unreadTab.removeClass("active");
        // Hide unread empty, show read empty (will be hidden if we have data)
        unreadEmpty.hide();
        readEmpty.show();
        // CRITICAL: Load read notifications AFTER setting currentTab
        var self = this;
        // Use setTimeout to ensure currentTab is set before AJAX callback
        setTimeout(function () {
          // Double-check we're still on read tab before loading
          if (self.currentTab === "read") {
            self.loadReadNotifications();
          }
        }, 0);
      }
    },

    loadUnreadNotifications: function () {
      var self = this;
      var url = "index.php?module=Vtiger&action=Notifications&type=unread";

      // CRITICAL: Double-check we're still on unread tab before making request
      if (this.currentTab !== "unread") {
        return;
      }

      jQuery.ajax({
        url: url,
        type: "GET",
        dataType: "json",
        cache: false, // Prevent Chrome cache issues
        xhrFields: {
          withCredentials: true // Ensure cookies are sent (required for Chrome)
        },
        success: function (response) {
          if (response && response.success) {
            // CRITICAL: Check again after AJAX completes
            if (self.currentTab === "unread") {
              self.updateUnreadUI(response);
              self.checkForNewNotifications(response.list);
            }
          }
        },
        error: function (xhr, status, error) {
          // Log error for debugging
          console.warn("[ModernNotifications] Error loading unread notifications:", status, error, xhr.status);
        },
      });
    },

    loadReadNotifications: function () {
      var self = this;
      var url = "index.php?module=Vtiger&action=Notifications&type=read";

      // CRITICAL: Double-check we're still on read tab before making request
      if (this.currentTab !== "read") {
        return;
      }

      jQuery.ajax({
        url: url,
        type: "GET",
        dataType: "json",
        cache: false, // Prevent Chrome cache issues
        xhrFields: {
          withCredentials: true // Ensure cookies are sent (required for Chrome)
        },
        success: function (response) {
          if (response && response.success) {
            // CRITICAL: Check again after AJAX completes
            if (self.currentTab === "read") {
              self.updateReadUI(response);
            }
          }
        },
        error: function (xhr, status, error) {
          // Log error for debugging
          console.warn("[ModernNotifications] Error loading read notifications:", status, error, xhr.status);
        },
      });
    },

    updateNotificationBadge: function (count) {
      var badge = document.getElementById("modern-notifications-count");
      if (!badge) return;

      var markAllBtn = document.getElementById(
        "modern-notifications-mark-all-read"
      );
      var countNum = Number(count) || 0;

      if (countNum > 0) {
        badge.textContent = countNum;
        badge.style.display = "";
        if (markAllBtn) {
          markAllBtn.style.display = "";
        }
      } else {
        badge.textContent = "";
        badge.style.display = "none";
        if (markAllBtn) {
          markAllBtn.style.display = "none";
        }
      }
    },

    updateUnreadUI: function (response) {
      // CRITICAL: Only update UI if we're on the unread tab
      if (this.currentTab !== "unread") {
        // Just update badge count, don't render list
        this.updateNotificationBadge(response.count || 0);
        return;
      }

      var count = response.count || 0;
      var list = response.list || [];
      var emptyMsg = jQuery("#modern-notifications-empty-unread");
      var readEmpty = jQuery("#modern-notifications-empty-read");
      var itemsContainer = jQuery("#modern-notifications-items");
      var actionsContainer = jQuery("#modern-notifications-actions");

      this.updateNotificationBadge(count);

      // CRITICAL: Ensure empty messages are shown/hidden correctly for unread tab
      readEmpty.hide(); // Always hide read empty when on unread tab

      // CRITICAL: Always clear items container first to remove any old data
      itemsContainer.empty();

      if (list.length === 0) {
        emptyMsg.show();
        itemsContainer.hide();
        actionsContainer.hide();
        this.lastRenderedNotificationId = 0;
        return;
      }

      // We have unread notifications - hide empty message and show items
      emptyMsg.hide();
      itemsContainer.show();
      actionsContainer.show();

      // CRITICAL: Always clear and re-render all unread notifications
      // (already cleared above, but ensure it's empty)
      itemsContainer.empty();
      var maxId = 0;
      for (var j = 0; j < list.length; j++) {
        // CRITICAL: Always render as unread (false) for unread tab
        this.renderNotificationItem(list[j], itemsContainer[0], false);
        var notifId = parseInt(list[j].id) || 0;
        if (notifId > maxId) {
          maxId = notifId;
        }
      }
      this.lastRenderedNotificationId = maxId;
    },

    updateReadUI: function (response) {
      // CRITICAL: Only update UI if we're on the read tab
      if (this.currentTab !== "read") {
        return;
      }

      var list = response.list || [];
      var emptyMsg = jQuery("#modern-notifications-empty-read");
      var unreadEmpty = jQuery("#modern-notifications-empty-unread");
      var itemsContainer = jQuery("#modern-notifications-items");
      var actionsContainer = jQuery("#modern-notifications-actions");

      // CRITICAL: Ensure empty messages are shown/hidden correctly for read tab
      unreadEmpty.hide(); // Always hide unread empty when on read tab

      // CRITICAL: Always clear items container first to remove any old data
      itemsContainer.empty();

      if (list.length === 0) {
        emptyMsg.show();
        itemsContainer.hide();
        actionsContainer.hide();
        return;
      }

      // We have read notifications - hide empty message and show items
      emptyMsg.hide();
      itemsContainer.show();
      actionsContainer.show();

      // CRITICAL: Always clear and re-render all read notifications
      // (already cleared above, but ensure it's empty)
      itemsContainer.empty();
      for (var i = 0; i < list.length; i++) {
        this.renderNotificationItem(list[i], itemsContainer[0], true);
      }
    },

    decodeHtmlEntities: function (text) {
      if (!text) return "";
      var textarea = document.createElement("textarea");
      textarea.innerHTML = text;
      return textarea.value;
    },

    highlightKeywords: function (text) {
      if (!text) return "";

      // Keywords to highlight with their colors
      // IMPORTANT: Longer patterns must come first to avoid partial matches
      // Each keyword has a UNIQUE color for easy identification
      var keywords = [
        { pattern: /Project Task:/gi, color: "#f39c12", fontWeight: "bold" }, // Vàng
        { pattern: /Organization:/gi, color: "#8e44ad", fontWeight: "bold" }, // Tím
        { pattern: /Ticket:/gi, color: "#e67e22", fontWeight: "bold" }, // Cam
        { pattern: /Contact:/gi, color: "#27ae60", fontWeight: "bold" }, // Xanh lá đậm
        { pattern: /Opportunity:/gi, color: "#1abc9c", fontWeight: "bold" }, // Xanh ngọc
        { pattern: /Task:/gi, color: "#3498db", fontWeight: "bold" }, // Xanh dương
        { pattern: /Event:/gi, color: "#e74c3c", fontWeight: "bold" }, // Đỏ
        { pattern: /Project:/gi, color: "#9b59b6", fontWeight: "bold" }, // Tím đậm
      ];

      var result = text;

      // Apply highlights in order (longer patterns first to avoid conflicts)
      keywords.forEach(function (keyword) {
        // Use a more robust replacement that preserves the matched text
        result = result.replace(keyword.pattern, function (match) {
          return (
            '<span class="notification-keyword" data-keyword="' +
            match.toLowerCase().replace(":", "") +
            '" style="color: ' +
            keyword.color +
            " !important; font-weight: " +
            keyword.fontWeight +
            ' !important; display: inline;">' +
            match +
            "</span>"
          );
        });
      });

      return result;
    },

    renderNotificationItem: function (notif, container, isRead) {
      var self = this;
      var module = notif.module || "Vtiger";
      var recordId = notif.recordid || "";
      var message = notif.message || "";
      var createdAt = notif.created_at || "";
      var notificationId = notif.id || "";
      var detailUrl = "";

      // Decode HTML entities in message and highlight keywords
      message = this.decodeHtmlEntities(message);
      var highlightedMessage = this.highlightKeywords(message);

      if (recordId) {
        detailUrl =
          "index.php?module=" + module + "&view=Detail&record=" + recordId;
      }

      var li = document.createElement("li");
      li.className = "modern-notification-item";
      // Add read-notification class only if isRead is true
      if (isRead) {
        li.classList.add("read-notification");
      }
      li.style.padding = "10px";
      li.style.borderBottom = "1px solid #eee";
      li.style.position = "relative";
      li.setAttribute("data-notification-id", notificationId);

      // Check if this is a deadline reminder notification
      var isDeadlineReminder =
        message.indexOf("sắp đến hạn") !== -1 ||
        message.indexOf("sắp hết hạn") !== -1;
      if (isDeadlineReminder) {
        li.classList.add("deadline-notification");
      }

      // Add checkbox - make it bolder and more visible
      var checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.className = "modern-notification-checkbox";
      checkbox.value = notificationId;
      checkbox.style.position = "absolute";
      // Move checkbox to the right if it's a deadline notification (to avoid warning icon)
      checkbox.style.left = isDeadlineReminder ? "35px" : "10px";
      checkbox.style.top = "15px";
      checkbox.style.cursor = "pointer";
      // Make checkbox bolder and more visible
      checkbox.style.width = "18px";
      checkbox.style.height = "18px";
      checkbox.style.border = "2px solid #333";
      checkbox.style.borderRadius = "3px";
      checkbox.style.accentColor = "#4CAF50";
      checkbox.addEventListener("click", function (e) {
        e.stopPropagation();
        self.updateDeleteButtonState();
      });
      li.appendChild(checkbox);

      // Content wrapper with left margin for checkbox and warning icon
      var contentWrapper = document.createElement("div");
      // Adjust margin based on whether it's a deadline notification
      contentWrapper.style.marginLeft = isDeadlineReminder ? "60px" : "25px";

      // Create link first if needed
      var link = null;
      if (detailUrl) {
        link = document.createElement("a");
        link.href = detailUrl;
        link.style.textDecoration = "none";
        link.style.color = "#333";
        link.style.display = "block";
      }

      if (isRead) {
        // Make read notifications more faded
        li.style.opacity = "0.5";
        li.style.cursor = "default";
        li.style.backgroundColor = "#f9f9f9";
        // Make text lighter
        if (link) {
          link.style.color = "#999";
        }
      } else {
        // Unread notifications - full opacity, bold, clickable
        li.style.opacity = "1";
        li.style.cursor = "pointer";
        li.style.backgroundColor = "";
        if (link) {
          link.style.color = "#333";
        }
        li.addEventListener("click", function (e) {
          if (
            e.target.tagName !== "INPUT" &&
            e.target.tagName !== "A" &&
            e.target.closest("a") === null
          ) {
            self.markAsRead(notificationId, li);
          }
        });
      }

      if (link) {
        var messageDiv = document.createElement("div");
        messageDiv.style.marginBottom = "5px";
        messageDiv.innerHTML = highlightedMessage;
        // Unread notifications are bold, read notifications are normal
        if (!isRead) {
          messageDiv.style.fontWeight = "bold";
          messageDiv.style.color = "#2c3e50";
        } else {
          messageDiv.style.fontWeight = "normal";
          messageDiv.style.color = "#999";
        }

        if (createdAt) {
          var dateDiv = document.createElement("div");
          dateDiv.style.fontSize = "11px";
          dateDiv.style.color = isRead ? "#bbb" : "#999";
          dateDiv.textContent = this.formatDate(createdAt);

          link.appendChild(messageDiv);
          link.appendChild(dateDiv);
          contentWrapper.appendChild(link);
        } else {
          link.appendChild(messageDiv);
          contentWrapper.appendChild(link);
        }
      } else {
        var messageDiv = document.createElement("div");
        messageDiv.style.marginBottom = "5px";
        messageDiv.innerHTML = highlightedMessage;
        // Unread notifications are bold, read notifications are normal
        if (!isRead) {
          messageDiv.style.fontWeight = "bold";
          messageDiv.style.color = "#2c3e50";
        } else {
          messageDiv.style.fontWeight = "normal";
          messageDiv.style.color = "#999";
        }

        if (createdAt) {
          var dateDiv = document.createElement("div");
          dateDiv.style.fontSize = "11px";
          dateDiv.style.color = "#999";
          dateDiv.textContent = this.formatDate(createdAt);

          contentWrapper.appendChild(messageDiv);
          contentWrapper.appendChild(dateDiv);
        } else {
          contentWrapper.appendChild(messageDiv);
        }
      }

      li.appendChild(contentWrapper);
      container.appendChild(li);
    },

    markAsRead: function (notificationId, element) {
      var self = this;
      var url = "index.php?module=Vtiger&action=MarkNotificationRead";

      if (element) {
        element.style.opacity = "0.5";
      }

      jQuery.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        cache: false, // Prevent Chrome cache issues
        xhrFields: {
          withCredentials: true // Ensure cookies are sent (required for Chrome)
        },
        data: {
          notification_id: notificationId,
        },
        success: function (response) {
          if (
            response &&
            response.success &&
            typeof response.unreadCount !== "undefined"
          ) {
            if (element && element.parentNode) {
              element.parentNode.removeChild(element);
            }

            var unreadCount = Number(response.unreadCount) || 0;
            self.updateNotificationBadge(unreadCount);

            var itemsContainer = jQuery("#modern-notifications-items");
            var emptyMsg = jQuery("#modern-notifications-empty-unread");

            if (unreadCount > 0) {
              itemsContainer.show();
              emptyMsg.hide();
            } else {
              itemsContainer.hide();
              emptyMsg.show();
            }

            var index = self.previousIds.indexOf(parseInt(notificationId));
            if (index > -1) {
              self.previousIds.splice(index, 1);
            }
          } else {
            if (element) {
              element.style.opacity = "1";
            }
          }
        },
        error: function (xhr, status, error) {
          // Log error for debugging
          console.warn("[ModernNotifications] Error marking notification as read:", status, error, xhr.status);
          if (element) {
            element.style.opacity = "1";
          }
        },
      });
    },

    markAllAsRead: function () {
      var self = this;
      var url =
        "index.php?module=Vtiger&action=MarkNotificationRead&mode=markAll";

      var itemsContainer = jQuery("#modern-notifications-items");
      itemsContainer.css("opacity", "0.5");

      jQuery.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        cache: false, // Prevent Chrome cache issues
        xhrFields: {
          withCredentials: true // Ensure cookies are sent (required for Chrome)
        },
        data: {
          mark_all: "true",
        },
        success: function (response) {
          if (
            response &&
            response.success &&
            typeof response.unreadCount !== "undefined"
          ) {
            // Clear unread list
            itemsContainer.empty();
            itemsContainer.hide();

            var unreadCount = Number(response.unreadCount) || 0;
            self.updateNotificationBadge(unreadCount);

            // Show empty message for unread tab
            var emptyMsgUnread = jQuery("#modern-notifications-empty-unread");
            emptyMsgUnread.show();

            // Switch to "Đã đọc" tab and load read notifications
            self.switchTab("read");
            self.loadReadNotifications();

            self.previousIds = [];
            self.lastRenderedNotificationId = 0;
          } else {
            itemsContainer.css("opacity", "1");
          }
        },
        error: function (xhr, status, error) {
          // Log error for debugging
          console.warn("[ModernNotifications] Error marking all as read:", status, error, xhr.status);
          itemsContainer.css("opacity", "1");
        },
      });
    },

    checkForNewNotifications: function (newList) {
      var newIds = [];
      for (var i = 0; i < newList.length; i++) {
        newIds.push(newList[i].id);
      }

      var hasNew = false;
      for (var j = 0; j < newIds.length; j++) {
        if (this.previousIds.indexOf(newIds[j]) === -1) {
          hasNew = true;
          this.playSound();
          // Add shake animation to bell icon
          this.shakeBell();
          break;
        }
      }

      this.previousIds = newIds;
    },

    shakeBell: function () {
      var bell = document.getElementById("modern-notifications-bell");
      if (!bell) return;

      // Remove existing shake class if any
      bell.classList.remove("bell-shake");

      // Force reflow
      void bell.offsetWidth;

      // Add shake animation
      bell.classList.add("bell-shake");

      // Remove after animation completes
      setTimeout(function () {
        bell.classList.remove("bell-shake");
      }, 1000);
    },

    playSound: function () {
      if (!this.sound) {
        return;
      }

      try {
        // Reset sound to beginning
        this.sound.currentTime = 0;

        // Play sound with promise handling
        var playPromise = this.sound.play();

        if (playPromise !== undefined) {
          playPromise
            .then(function () {
              // Sound played successfully
            })
            .catch(function (error) {
              // Autoplay was prevented or sound failed
              console.warn("[ModernNotifications] Sound play failed:", error);
              // Try to create a new Audio instance as fallback
              try {
                var baseUrl = "";
                if (typeof _META !== "undefined" && _META.notifier) {
                  var notifierUrl = _META.notifier;
                  baseUrl = notifierUrl.substring(
                    0,
                    notifierUrl.lastIndexOf("/")
                  );
                } else {
                  var pathParts = window.location.pathname.split("/");
                  pathParts = pathParts.filter(function (part) {
                    return part && part !== "index.php";
                  });
                  baseUrl = window.location.origin;
                  if (pathParts.length > 0 && pathParts[0] !== "") {
                    baseUrl += "/" + pathParts[0];
                  }
                }
                if (baseUrl.endsWith("/")) {
                  baseUrl = baseUrl.slice(0, -1);
                }
                var soundPath =
                  baseUrl +
                  "/layouts/v7/modules/Vtiger/resources/sounds/notification.mp3";
                var fallbackSound = new Audio(soundPath);
                fallbackSound.volume = 0.7;
                fallbackSound.play().catch(function (e) {
                  console.warn(
                    "[ModernNotifications] Fallback sound also failed"
                  );
                });
              } catch (e) {
                console.warn(
                  "[ModernNotifications] Fallback sound creation failed"
                );
              }
            });
        }
      } catch (e) {
        console.warn("[ModernNotifications] Sound play error:", e);
      }
    },

    formatDate: function (dateString) {
      if (!dateString) return "";
      try {
        var date = new Date(dateString);
        var now = new Date();
        var diff = Math.floor((now - date) / 1000);

        if (diff < 60) {
          return "Vừa xong";
        } else if (diff < 3600) {
          return Math.floor(diff / 60) + " phút trước";
        } else if (diff < 86400) {
          return Math.floor(diff / 3600) + " giờ trước";
        } else {
          return date.toLocaleDateString("vi-VN");
        }
      } catch (e) {
        return dateString;
      }
    },

    updateDeleteButtonState: function () {
      var checkedBoxes = jQuery(".modern-notification-checkbox:checked");
      var deleteSelectedBtn = jQuery("#modern-notifications-delete-selected");
      if (checkedBoxes.length > 0) {
        deleteSelectedBtn.show();
      } else {
        deleteSelectedBtn.hide();
      }
    },

    deleteSelectedNotifications: function () {
      var self = this;
      var checkedBoxes = jQuery(".modern-notification-checkbox:checked");
      if (checkedBoxes.length === 0) {
        return;
      }

      var notificationIds = [];
      checkedBoxes.each(function () {
        notificationIds.push(parseInt(this.value));
      });

      var url = "index.php?module=Vtiger&action=DeleteNotification";
      jQuery.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        cache: false, // Prevent Chrome cache issues
        xhrFields: {
          withCredentials: true // Ensure cookies are sent (required for Chrome)
        },
        data: {
          mode: "deleteSelected",
          notification_ids: notificationIds,
        },
        success: function (response) {
          if (response && response.success) {
            // Reload current tab
            if (self.currentTab === "unread") {
              self.loadUnreadNotifications();
            } else {
              self.loadReadNotifications();
            }
            self.updateDeleteButtonState();
          }
        },
        error: function (xhr, status, error) {
          // Log error for debugging
          console.warn("[ModernNotifications] Error deleting selected notifications:", status, error, xhr.status);
        },
      });
    },

    deleteAllNotifications: function () {
      var self = this;
      if (!confirm("Bạn có chắc chắn muốn xóa tất cả thông báo?")) {
        return;
      }

      var url = "index.php?module=Vtiger&action=DeleteNotification";
      jQuery.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        cache: false, // Prevent Chrome cache issues
        xhrFields: {
          withCredentials: true // Ensure cookies are sent (required for Chrome)
        },
        data: {
          mode: "deleteAll",
        },
        success: function (response) {
          if (response && response.success) {
            // Reload current tab
            if (self.currentTab === "unread") {
              self.loadUnreadNotifications();
            } else {
              self.loadReadNotifications();
            }
            self.updateDeleteButtonState();
          }
        },
        error: function (xhr, status, error) {
          // Log error for debugging
          console.warn("[ModernNotifications] Error deleting all notifications:", status, error, xhr.status);
        },
      });
    },

    destroy: function () {
      if (this.intervalId) {
        clearInterval(this.intervalId);
        this.intervalId = null;
      }
      this.initialized = false;
      this.lastRenderedNotificationId = 0;
      this.previousIds = [];
      this.currentTab = "unread";
    },
  };

  jQuery(document).ready(function () {
    ModernNotifications.init();

    jQuery(document).on(
      "click",
      "#modern-notifications-mark-all-read",
      function (e) {
        e.preventDefault();
        e.stopPropagation();
        ModernNotifications.markAllAsRead();
      }
    );

    jQuery(document).on(
      "click",
      "#modern-notifications-delete-selected",
      function (e) {
        e.preventDefault();
        e.stopPropagation();
        ModernNotifications.deleteSelectedNotifications();
      }
    );

    jQuery(document).on(
      "click",
      "#modern-notifications-delete-all",
      function (e) {
        e.preventDefault();
        e.stopPropagation();
        ModernNotifications.deleteAllNotifications();
      }
    );
  });

  window.ModernNotifications = ModernNotifications;
})();
