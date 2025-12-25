(function() {
	'use strict';

	var ModernNotifications = {
		previousIds: [],
		lastRenderedNotificationId: 0,
		intervalId: null,
		sound: null,
		initialized: false,
		currentTab: 'unread',

		init: function() {
			if (this.initialized) {
				return;
			}

			// Initialize sound with proper path
			this.initSound();

			this.loadUnreadNotifications();
			this.intervalId = setInterval(function() {
				if (ModernNotifications.currentTab === 'unread') {
					ModernNotifications.loadUnreadNotifications();
				}
			}, 3000);

			this.setupTabHandlers();

			this.initialized = true;
		},

		initSound: function() {
			try {
				// Get base URL - try multiple methods
				var baseUrl = '';
				if (typeof _META !== 'undefined' && _META.notifier) {
					// Try to extract base URL from Vtiger's _META
					var notifierUrl = _META.notifier;
					baseUrl = notifierUrl.substring(0, notifierUrl.lastIndexOf('/'));
				} else {
					// Fallback: construct from current location
					var pathParts = window.location.pathname.split('/');
					pathParts = pathParts.filter(function(part) {
						return part && part !== 'index.php';
					});
					baseUrl = window.location.origin;
					if (pathParts.length > 0 && pathParts[0] !== '') {
						baseUrl += '/' + pathParts[0];
					}
				}
				
				// Remove trailing slash
				if (baseUrl.endsWith('/')) {
					baseUrl = baseUrl.slice(0, -1);
				}
				
				var soundPath = baseUrl + '/layouts/v7/modules/Vtiger/resources/sounds/notification.mp3';
				
				this.sound = new Audio(soundPath);
				this.sound.volume = 0.7;
				this.sound.preload = 'auto';
				
				// Preload sound on user interaction (required by browsers for autoplay)
				var self = this;
				var preloadSound = function() {
					if (self.sound) {
						self.sound.load().catch(function(e) {
							console.warn('[ModernNotifications] Sound preload failed:', e);
						});
					}
					document.removeEventListener('click', preloadSound);
					document.removeEventListener('touchstart', preloadSound);
					document.removeEventListener('keydown', preloadSound);
				};
				document.addEventListener('click', preloadSound, { once: true });
				document.addEventListener('touchstart', preloadSound, { once: true });
				document.addEventListener('keydown', preloadSound, { once: true });
			} catch (e) {
				console.warn('[ModernNotifications] Sound initialization failed:', e);
			}
		},

		setupTabHandlers: function() {
			var self = this;
			jQuery('#modern-notifications-tab-unread-link').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				self.switchTab('unread');
			});

			jQuery('#modern-notifications-tab-read-link').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				self.switchTab('read');
			});
		},

		switchTab: function(tab) {
			if (this.currentTab === tab) {
				return;
			}

			this.currentTab = tab;

			var unreadTab = jQuery('#modern-notifications-tab-unread');
			var readTab = jQuery('#modern-notifications-tab-read');
			var unreadEmpty = jQuery('#modern-notifications-empty-unread');
			var readEmpty = jQuery('#modern-notifications-empty-read');
			var itemsContainer = jQuery('#modern-notifications-items');

			if (tab === 'unread') {
				unreadTab.addClass('active');
				readTab.removeClass('active');
				unreadEmpty.show();
				readEmpty.hide();
				this.loadUnreadNotifications();
			} else {
				readTab.addClass('active');
				unreadTab.removeClass('active');
				readEmpty.show();
				unreadEmpty.hide();
				itemsContainer.hide();
				this.loadReadNotifications();
			}
		},

		loadUnreadNotifications: function() {
			var self = this;
			var url = 'index.php?module=Vtiger&action=Notifications&type=unread';

			jQuery.ajax({
				url: url,
				type: 'GET',
				dataType: 'json',
				success: function(response) {
					if (response && response.success) {
						self.updateUnreadUI(response);
						self.checkForNewNotifications(response.list);
					}
				},
				error: function(xhr, status, error) {
				}
			});
		},

		loadReadNotifications: function() {
			var self = this;
			var url = 'index.php?module=Vtiger&action=Notifications&type=read';

			jQuery.ajax({
				url: url,
				type: 'GET',
				dataType: 'json',
				success: function(response) {
					if (response && response.success) {
						self.updateReadUI(response);
					}
				},
				error: function(xhr, status, error) {
				}
			});
		},

		updateNotificationBadge: function(count) {
			var badge = document.getElementById('modern-notifications-count');
			if (!badge) return;

			var markAllBtn = document.getElementById('modern-notifications-mark-all-read');
			var countNum = Number(count) || 0;

			if (countNum > 0) {
				badge.textContent = countNum;
				badge.style.display = '';
				if (markAllBtn) {
					markAllBtn.style.display = '';
				}
			} else {
				badge.textContent = '';
				badge.style.display = 'none';
				if (markAllBtn) {
					markAllBtn.style.display = 'none';
				}
			}
		},

		updateUnreadUI: function(response) {
			var count = response.count || 0;
			var list = response.list || [];
			var emptyMsg = jQuery('#modern-notifications-empty-unread');
			var itemsContainer = jQuery('#modern-notifications-items');
			var actionsContainer = jQuery('#modern-notifications-actions');

			this.updateNotificationBadge(count);

			if (list.length === 0) {
				emptyMsg.show();
				itemsContainer.hide();
				actionsContainer.hide();
				this.lastRenderedNotificationId = 0;
				return;
			}

			emptyMsg.hide();
			itemsContainer.show();
			actionsContainer.show();

			var newNotifications = [];
			var maxId = this.lastRenderedNotificationId;

			for (var i = 0; i < list.length; i++) {
				var notif = list[i];
				var notifId = parseInt(notif.id) || 0;
				
				if (notifId > this.lastRenderedNotificationId) {
					newNotifications.push(notif);
					if (notifId > maxId) {
						maxId = notifId;
					}
				}
			}

			if (newNotifications.length === 0 && this.lastRenderedNotificationId === 0) {
				itemsContainer.empty();
				for (var j = 0; j < list.length; j++) {
					this.renderNotificationItem(list[j], itemsContainer[0], false);
					var notifId = parseInt(list[j].id) || 0;
					if (notifId > maxId) {
						maxId = notifId;
					}
				}
				this.lastRenderedNotificationId = maxId;
				return;
			}

			if (newNotifications.length > 0) {
				var fragment = document.createDocumentFragment();
				for (var k = 0; k < newNotifications.length; k++) {
					var notif = newNotifications[k];
					this.renderNotificationItem(notif, fragment, false);
				}
				
				if (itemsContainer[0].firstChild) {
					itemsContainer[0].insertBefore(fragment, itemsContainer[0].firstChild);
				} else {
					itemsContainer[0].appendChild(fragment);
				}

				this.lastRenderedNotificationId = maxId;
			}
		},

		updateReadUI: function(response) {
			var list = response.list || [];
			var emptyMsg = jQuery('#modern-notifications-empty-read');
			var itemsContainer = jQuery('#modern-notifications-items');
			var actionsContainer = jQuery('#modern-notifications-actions');

			if (list.length === 0) {
				emptyMsg.show();
				itemsContainer.hide();
				actionsContainer.hide();
				return;
			}

			emptyMsg.hide();
			itemsContainer.show();
			actionsContainer.show();
			itemsContainer.empty();

			for (var i = 0; i < list.length; i++) {
				this.renderNotificationItem(list[i], itemsContainer[0], true);
			}
		},

		decodeHtmlEntities: function(text) {
			if (!text) return '';
			var textarea = document.createElement('textarea');
			textarea.innerHTML = text;
			return textarea.value;
		},

		highlightKeywords: function(text) {
			if (!text) return '';
			
			// Keywords to highlight with their colors
			var keywords = [
				{ pattern: /(Task:)/gi, color: '#3498db', fontWeight: 'bold' },
				{ pattern: /(Event:)/gi, color: '#e74c3c', fontWeight: 'bold' },
				{ pattern: /(Project:)/gi, color: '#9b59b6', fontWeight: 'bold' },
				{ pattern: /(Project Task:)/gi, color: '#f39c12', fontWeight: 'bold' },
				{ pattern: /(Opportunity:)/gi, color: '#1abc9c', fontWeight: 'bold' }
			];

			var result = text;
			
			// Apply highlights in order (longer patterns first to avoid conflicts)
			keywords.forEach(function(keyword) {
				result = result.replace(keyword.pattern, function(match) {
					return '<span class="notification-keyword" style="color: ' + keyword.color + '; font-weight: ' + keyword.fontWeight + ';">' + match + '</span>';
				});
			});

			return result;
		},

		renderNotificationItem: function(notif, container, isRead) {
			var self = this;
			var module = notif.module || 'Vtiger';
			var recordId = notif.recordid || '';
			var message = notif.message || '';
			var createdAt = notif.created_at || '';
			var notificationId = notif.id || '';
			var detailUrl = '';

			// Decode HTML entities in message and highlight keywords
			message = this.decodeHtmlEntities(message);
			var highlightedMessage = this.highlightKeywords(message);

			if (recordId) {
				detailUrl = 'index.php?module=' + module + '&view=Detail&record=' + recordId;
			}

			var li = document.createElement('li');
			li.className = 'modern-notification-item';
			li.style.padding = '10px';
			li.style.borderBottom = '1px solid #eee';
			li.style.position = 'relative';
			li.setAttribute('data-notification-id', notificationId);

			// Add checkbox
			var checkbox = document.createElement('input');
			checkbox.type = 'checkbox';
			checkbox.className = 'modern-notification-checkbox';
			checkbox.value = notificationId;
			checkbox.style.position = 'absolute';
			checkbox.style.left = '10px';
			checkbox.style.top = '15px';
			checkbox.style.cursor = 'pointer';
			checkbox.addEventListener('click', function(e) {
				e.stopPropagation();
				self.updateDeleteButtonState();
			});
			li.appendChild(checkbox);

			// Content wrapper with left margin for checkbox
			var contentWrapper = document.createElement('div');
			contentWrapper.style.marginLeft = '25px';

			if (isRead) {
				li.style.opacity = '0.6';
				li.style.cursor = 'default';
			} else {
				li.style.cursor = 'pointer';
				li.addEventListener('click', function(e) {
					if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'A' && e.target.closest('a') === null) {
						self.markAsRead(notificationId, li);
					}
				});
			}

			if (detailUrl) {
				var link = document.createElement('a');
				link.href = detailUrl;
				link.style.textDecoration = 'none';
				link.style.color = '#333';
				link.style.display = 'block';

				var messageDiv = document.createElement('div');
				messageDiv.style.marginBottom = '5px';
				messageDiv.innerHTML = highlightedMessage;
				if (!isRead) {
					messageDiv.style.fontWeight = 'bold';
				}

				if (createdAt) {
					var dateDiv = document.createElement('div');
					dateDiv.style.fontSize = '11px';
					dateDiv.style.color = '#999';
					dateDiv.textContent = this.formatDate(createdAt);

					link.appendChild(messageDiv);
					link.appendChild(dateDiv);
					contentWrapper.appendChild(link);
				} else {
					link.appendChild(messageDiv);
					contentWrapper.appendChild(link);
				}
			} else {
				var messageDiv = document.createElement('div');
				messageDiv.style.marginBottom = '5px';
				messageDiv.innerHTML = highlightedMessage;
				if (!isRead) {
					messageDiv.style.fontWeight = 'bold';
				}

				if (createdAt) {
					var dateDiv = document.createElement('div');
					dateDiv.style.fontSize = '11px';
					dateDiv.style.color = '#999';
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

		markAsRead: function(notificationId, element) {
			var self = this;
			var url = 'index.php?module=Vtiger&action=MarkNotificationRead';

			if (element) {
				element.style.opacity = '0.5';
			}

			jQuery.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				data: {
					notification_id: notificationId
				},
				success: function(response) {
					if (response && response.success && typeof response.unreadCount !== 'undefined') {
						if (element && element.parentNode) {
							element.parentNode.removeChild(element);
						}

						var unreadCount = Number(response.unreadCount) || 0;
						self.updateNotificationBadge(unreadCount);

						var itemsContainer = jQuery('#modern-notifications-items');
						var emptyMsg = jQuery('#modern-notifications-empty-unread');

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
							element.style.opacity = '1';
						}
					}
				},
				error: function(xhr, status, error) {
					if (element) {
						element.style.opacity = '1';
					}
				}
			});
		},

		markAllAsRead: function() {
			var self = this;
			var url = 'index.php?module=Vtiger&action=MarkNotificationRead&mode=markAll';

			var itemsContainer = jQuery('#modern-notifications-items');
			itemsContainer.css('opacity', '0.5');

			jQuery.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				data: {
					mark_all: 'true'
				},
				success: function(response) {
					if (response && response.success && typeof response.unreadCount !== 'undefined') {
						itemsContainer.empty();
						itemsContainer.hide();

						var unreadCount = Number(response.unreadCount) || 0;
						self.updateNotificationBadge(unreadCount);

						var emptyMsg = jQuery('#modern-notifications-empty-unread');
						emptyMsg.show();

						self.previousIds = [];
						self.lastRenderedNotificationId = 0;
					} else {
						itemsContainer.css('opacity', '1');
					}
				},
				error: function(xhr, status, error) {
					itemsContainer.css('opacity', '1');
				}
			});
		},

		checkForNewNotifications: function(newList) {
			var newIds = [];
			for (var i = 0; i < newList.length; i++) {
				newIds.push(newList[i].id);
			}

			for (var j = 0; j < newIds.length; j++) {
				if (this.previousIds.indexOf(newIds[j]) === -1) {
					this.playSound();
					break;
				}
			}

			this.previousIds = newIds;
		},

		playSound: function() {
			if (!this.sound) {
				return;
			}

			try {
				// Reset sound to beginning
				this.sound.currentTime = 0;
				
				// Play sound with promise handling
				var playPromise = this.sound.play();
				
				if (playPromise !== undefined) {
					playPromise.then(function() {
						// Sound played successfully
					}).catch(function(error) {
						// Autoplay was prevented or sound failed
						console.warn('[ModernNotifications] Sound play failed:', error);
						// Try to create a new Audio instance as fallback
						try {
							var baseUrl = '';
							if (typeof _META !== 'undefined' && _META.notifier) {
								var notifierUrl = _META.notifier;
								baseUrl = notifierUrl.substring(0, notifierUrl.lastIndexOf('/'));
							} else {
								var pathParts = window.location.pathname.split('/');
								pathParts = pathParts.filter(function(part) {
									return part && part !== 'index.php';
								});
								baseUrl = window.location.origin;
								if (pathParts.length > 0 && pathParts[0] !== '') {
									baseUrl += '/' + pathParts[0];
								}
							}
							if (baseUrl.endsWith('/')) {
								baseUrl = baseUrl.slice(0, -1);
							}
							var soundPath = baseUrl + '/layouts/v7/modules/Vtiger/resources/sounds/notification.mp3';
							var fallbackSound = new Audio(soundPath);
							fallbackSound.volume = 0.7;
							fallbackSound.play().catch(function(e) {
								console.warn('[ModernNotifications] Fallback sound also failed');
							});
						} catch (e) {
							console.warn('[ModernNotifications] Fallback sound creation failed');
						}
					});
				}
			} catch (e) {
				console.warn('[ModernNotifications] Sound play error:', e);
			}
		},

		formatDate: function(dateString) {
			if (!dateString) return '';
			try {
				var date = new Date(dateString);
				var now = new Date();
				var diff = Math.floor((now - date) / 1000);

				if (diff < 60) {
					return 'Vừa xong';
				} else if (diff < 3600) {
					return Math.floor(diff / 60) + ' phút trước';
				} else if (diff < 86400) {
					return Math.floor(diff / 3600) + ' giờ trước';
				} else {
					return date.toLocaleDateString('vi-VN');
				}
			} catch (e) {
				return dateString;
			}
		},

		updateDeleteButtonState: function() {
			var checkedBoxes = jQuery('.modern-notification-checkbox:checked');
			var deleteSelectedBtn = jQuery('#modern-notifications-delete-selected');
			if (checkedBoxes.length > 0) {
				deleteSelectedBtn.show();
			} else {
				deleteSelectedBtn.hide();
			}
		},

		deleteSelectedNotifications: function() {
			var self = this;
			var checkedBoxes = jQuery('.modern-notification-checkbox:checked');
			if (checkedBoxes.length === 0) {
				return;
			}

			var notificationIds = [];
			checkedBoxes.each(function() {
				notificationIds.push(parseInt(this.value));
			});

			var url = 'index.php?module=Vtiger&action=DeleteNotification';
			jQuery.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				data: {
					mode: 'deleteSelected',
					notification_ids: notificationIds
				},
				success: function(response) {
					if (response && response.success) {
						// Reload current tab
						if (self.currentTab === 'unread') {
							self.loadUnreadNotifications();
						} else {
							self.loadReadNotifications();
						}
						self.updateDeleteButtonState();
					}
				},
				error: function(xhr, status, error) {
				}
			});
		},

		deleteAllNotifications: function() {
			var self = this;
			if (!confirm('Bạn có chắc chắn muốn xóa tất cả thông báo?')) {
				return;
			}

			var url = 'index.php?module=Vtiger&action=DeleteNotification';
			jQuery.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				data: {
					mode: 'deleteAll'
				},
				success: function(response) {
					if (response && response.success) {
						// Reload current tab
						if (self.currentTab === 'unread') {
							self.loadUnreadNotifications();
						} else {
							self.loadReadNotifications();
						}
						self.updateDeleteButtonState();
					}
				},
				error: function(xhr, status, error) {
				}
			});
		},

		destroy: function() {
			if (this.intervalId) {
				clearInterval(this.intervalId);
				this.intervalId = null;
			}
			this.initialized = false;
			this.lastRenderedNotificationId = 0;
			this.previousIds = [];
			this.currentTab = 'unread';
		}
	};

	jQuery(document).ready(function() {
		ModernNotifications.init();

		jQuery(document).on('click', '#modern-notifications-mark-all-read', function(e) {
			e.preventDefault();
			e.stopPropagation();
			ModernNotifications.markAllAsRead();
		});

		jQuery(document).on('click', '#modern-notifications-delete-selected', function(e) {
			e.preventDefault();
			e.stopPropagation();
			ModernNotifications.deleteSelectedNotifications();
		});

		jQuery(document).on('click', '#modern-notifications-delete-all', function(e) {
			e.preventDefault();
			e.stopPropagation();
			ModernNotifications.deleteAllNotifications();
		});
	});

	window.ModernNotifications = ModernNotifications;

})();
