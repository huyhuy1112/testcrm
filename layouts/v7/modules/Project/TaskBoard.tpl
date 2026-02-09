{* Task Board view for Project *}
{strip}
<div class="project-task-board">
    <div class="board-toolbar">
        <div class="btn-group">
            <a class="btn btn-default btn-sm board-add-main" href="{$CREATE_TASK_URL}">
                <i class="fa fa-plus"></i> {vtranslate('LBL_ADD', $MODULE)}
            </a>
        </div>
        <div class="board-tabs">
            <button class="btn btn-default btn-sm active"><i class="fa fa-th-large"></i> Board</button>
            <button class="btn btn-default btn-sm"><i class="fa fa-table"></i> Table</button>
        </div>
    </div>

    <div class="board-columns">
        {foreach from=$TASK_COLUMNS key=STATUS_LABEL item=TASKS}
            <div class="board-column" data-status="{$STATUS_LABEL}">
                <div class="board-column-header">
                    <span class="status-name">{$STATUS_LABEL}</span>
                    <span class="status-actions">
                        <span class="status-count">{php7_count($TASKS)}</span>
                        <a class="board-add-task"
                           href="{$CREATE_TASK_URL}&projecttaskstatus={$STATUS_MAP[$STATUS_LABEL]|escape:'url'}">
                            <i class="fa fa-plus"></i>
                        </a>
                    </span>
                </div>
                <div class="board-cards" data-status="{$STATUS_LABEL}" data-status-value="{$STATUS_MAP[$STATUS_LABEL]}">
                    {foreach from=$TASKS item=TASK}
                        <div class="board-card" draggable="true" data-task='{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($TASK))}'>
                            <div class="card-title">{$TASK.name}</div>
                            <div class="card-meta">
                                <span class="meta-item"><i class="fa fa-calendar"></i> Due date {if $TASK.enddate}{$TASK.enddate}{else}--{/if}</span>
                                <span class="meta-item"><i class="fa fa-user"></i> Assignees {$TASK.owner_name|default:'--'} <i class="fa fa-plus card-add-assignee" title="Add assignee"></i></span>
                            </div>
                            <div class="card-footer">
                                <span class="card-post-time">
                                    {if $TASK.createdtime_display}{$TASK.createdtime_display}{/if}
                                    {if $TASK.comment_count > 0}<i class="fa fa-comment-o card-comment-icon"></i> {$TASK.comment_count}{/if}
                                </span>
                                <span class="card-progress-value">{if $TASK.progress}{$TASK.progress}{else}0%{/if}</span>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        {/foreach}
    </div>

    <div class="task-detail-modal hidden">
        <div class="task-detail-dialog">
            <div class="task-detail-header">
                <div class="header-left">
                    <span class="status-pill detail-status">--</span>
                    <span class="detail-id"></span>
                </div>
                <div class="header-right">
                    <button class="tab-btn active">Comments</button>
                    <button class="tab-btn">Task history</button>
                    <span class="panel-close">&times;</span>
                </div>
            </div>
            <div class="task-detail-content">
                <div class="task-detail-left">
                    <div class="detail-breadcrumb">Onboarding project › List of tasks</div>
                    <div class="detail-title"></div>
                    <div class="detail-section">
                        <div class="section-label">Description</div>
                        <textarea class="form-control detail-description" rows="3" placeholder="Write description..."></textarea>
                    </div>
                    <div class="detail-table">
                        <div class="detail-row">
                            <span class="label">Start/Due</span>
                            <span class="value detail-dates">
                                <input type="date" class="detail-start" />
                                <span class="date-sep">→</span>
                                <input type="date" class="detail-end" />
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Labels</span>
                            <span class="value"><input type="text" class="detail-labels" placeholder="Select" /></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Assignees</span>
                            <span class="value">
                                <select class="detail-owner-select">
                                    {foreach from=$TASK_USERS key=USER_ID item=USER_NAME}
                                        <option value="{$USER_ID}">{$USER_NAME|escape:'html'}</option>
                                    {/foreach}
                                </select>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Time</span>
                            <span class="value"><input type="text" class="detail-time" placeholder="Add logged time / Add estimated time" /></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Progress</span>
                            <span class="value">
                                <input type="number" min="0" max="100" class="detail-progress" />
                                <span class="progress-suffix">%</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Status</span>
                            <span class="value">
                                <select class="detail-status-select">
                                    {foreach from=$TASK_STATUS item=STATUS}
                                        <option value="{$STATUS|escape:'html'}">{$STATUS|escape:'html'}</option>
                                    {/foreach}
                                </select>
                            </span>
                        </div>
                        <div class="detail-row">
                            <a class="detail-link" href="javascript:void(0)">Add field</a>
                            <span>or</span>
                            <a class="detail-link" href="javascript:void(0)">Manage fields</a>
                        </div>
                    </div>
                    <div class="detail-subtasks">
                        <div class="section-label">Subtasks</div>
                        <div class="subtask-input">
                            <span class="subtask-icon"></span>
                            <input type="text" placeholder="Add task and hit enter/return key" />
                        </div>
                        <div class="subtask-empty">No subtasks exist in this task</div>
                    </div>
                </div>
                <div class="task-detail-right">
                    <div class="ann-detail-tabs">
                        <button type="button" class="ann-tab task-detail-tab active" data-tab="comments">Comments <span class="badge task-comments-badge">0</span></button>
                        <button type="button" class="ann-tab task-detail-tab" data-tab="history">Task history</button>
                    </div>
                    <div id="task-panel-comments" class="ann-detail-panel task-detail-panel">
                        <ul class="ann-comments-list list-unstyled task-comments-list"></ul>
                        <div class="ann-add-comment">
                            <textarea class="form-control task-comment-input" rows="2" placeholder="Write a comment"></textarea>
                            <button type="button" class="btn btn-primary btn-sm task-comment-add">Add</button>
                        </div>
                    </div>
                    <div id="task-panel-history" class="ann-detail-panel task-detail-panel hide">
                        <ul class="task-history-list list-unstyled"></ul>
                        <div class="task-history-empty text-muted small">No history yet.</div>
                    </div>
                </div>
            </div>
            <div class="task-detail-footer">
                <button class="btn btn-primary detail-save">Save</button>
                <button class="btn btn-default detail-cancel">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var board = document.querySelector('.project-task-board');
    if (!board) return;
    var panel = board.querySelector('.task-detail-modal');
    var closeBtn = board.querySelector('.panel-close');
    var titleEl = board.querySelector('.detail-title');
    var descEl = board.querySelector('.detail-description');
    var startEl = board.querySelector('.detail-start');
    var endEl = board.querySelector('.detail-end');
    var ownerSelect = board.querySelector('.detail-owner-select');
    var progressEl = board.querySelector('.detail-progress');
    var statusEl = board.querySelector('.detail-status');
    var idEl = board.querySelector('.detail-id');
    var statusSelect = board.querySelector('.detail-status-select');
    var saveBtn = board.querySelector('.detail-save');
    var commentList = board.querySelector('.task-comments-list');
    var commentInput = board.querySelector('.task-comment-input');
    var commentAddBtn = board.querySelector('.task-comment-add');
    var commentsBadge = board.querySelector('.task-comments-badge');
    var currentTask = null;

    function renderTaskComments(comments) {
        if (!commentList) return;
        commentList.innerHTML = '';
        (comments || []).forEach(function (c) {
            var name = c.userName || '';
            var initial = name ? name.charAt(0).toUpperCase() : '?';
            var text = (c.comment_text || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            var time = c.time || '';
            var item = document.createElement('li');
            item.className = 'ann-comment-item';
            item.innerHTML = '<span class="ann-avatar ann-avatar-user ann-avatar-sm">' + initial + '</span>' +
                '<span class="ann-comment-meta">' + name + ' ' + time + '</span>' +
                '<div class="ann-comment-text">' + text + '</div>';
            commentList.appendChild(item);
        });
        if (commentsBadge) commentsBadge.textContent = (comments || []).length;
    }

    function loadTaskComments(taskId) {
        if (!taskId || typeof app === 'undefined' || !app.request) return;
        app.request.post({ data: { module: 'ProjectTask', action: 'GetComments', record: taskId } }).then(function (err, data) {
            if (err) return;
            var res = (data && data.result) ? data.result : (data || {});
            renderTaskComments(res.comments || []);
        });
    }

    board.addEventListener('click', function (e) {
        var card = e.target.closest('.board-card');
        if (!card) return;
        var data = card.getAttribute('data-task');
        if (!data) return;
        var task = JSON.parse(data);
        currentTask = task;
        titleEl.textContent = task.name || '';
        if (descEl) descEl.value = task.description || '';
        if (startEl) startEl.value = task.startdate || '';
        if (endEl) endEl.value = task.enddate || '';
        if (ownerSelect && task.smownerid) ownerSelect.value = task.smownerid;
        if (progressEl) progressEl.value = (task.progress || '0').toString().replace('%', '');
        if (statusEl) statusEl.textContent = task.projecttaskstatus || '--';
        if (statusSelect) statusSelect.value = task.projecttaskstatus || '';
        idEl.textContent = task.recordid ? ('#' + task.recordid) : '';
        panel.classList.remove('hidden');
        loadTaskComments(task.recordid);
        switchTaskDetailTab('comments');
    });

    function switchTaskDetailTab(tab) {
        var commentsPanel = board.querySelector('#task-panel-comments');
        var historyPanel = board.querySelector('#task-panel-history');
        var tabs = board.querySelectorAll('.task-detail-tab');
        tabs.forEach(function (t) {
            t.classList.toggle('active', t.getAttribute('data-tab') === tab);
        });
        if (commentsPanel) commentsPanel.classList.toggle('hide', tab !== 'comments');
        if (historyPanel) historyPanel.classList.toggle('hide', tab !== 'history');
        if (tab === 'history' && currentTask && currentTask.recordid) {
            loadTaskHistory(currentTask.recordid);
        }
    }

    function loadTaskHistory(taskId) {
        var historyList = board.querySelector('.task-history-list');
        var historyEmpty = board.querySelector('.task-history-empty');
        if (!historyList || typeof app === 'undefined' || !app.request) return;
        app.request.post({ data: { module: 'ProjectTask', action: 'GetHistory', record: taskId } }).then(function (err, data) {
            if (err) return;
            var res = (data && data.result) ? data.result : (data || {});
            var history = res.history || [];
            historyList.innerHTML = '';
            if (historyEmpty) historyEmpty.style.display = history.length ? 'none' : 'block';
            history.forEach(function (h) {
                var name = h.userName || '';
                var initial = name ? name.charAt(0).toUpperCase() : '?';
                var time = h.time || '';
                var action = h.action || 'Updated';
                var changesHtml = '';
                (h.changes || []).forEach(function (c) {
                    changesHtml += '<div class="history-change">' + (c.field || '') + ': ' + (c.pre || '-') + ' → ' + (c.post || '-') + '</div>';
                });
                var item = document.createElement('li');
                item.className = 'ann-comment-item task-history-item';
                item.innerHTML = '<span class="ann-avatar ann-avatar-user ann-avatar-sm">' + initial + '</span>' +
                    '<span class="ann-comment-meta">' + name + ' · ' + action + ' · ' + time + '</span>' +
                    '<div class="ann-comment-text">' + changesHtml + '</div>';
                historyList.appendChild(item);
            });
        });
    }

    board.querySelectorAll('.task-detail-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            var t = this.getAttribute('data-tab');
            if (t) switchTaskDetailTab(t);
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            panel.classList.add('hidden');
        });
    }
    var cancelBtn = board.querySelector('.detail-cancel');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            panel.classList.add('hidden');
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            if (!currentTask || typeof app === 'undefined' || !app.request) return;
            var progressVal = progressEl ? progressEl.value : '';
            if (progressVal !== '' && progressVal.toString().indexOf('%') === -1) {
                progressVal = progressVal + '%';
            }
            var payload = {
                module: 'ProjectTask',
                action: 'SaveTask',
                record: currentTask.recordid,
                projecttaskname: currentTask.name || '',
                projectid: currentTask.projectid || '',
                startdate: startEl ? startEl.value : '',
                enddate: endEl ? endEl.value : '',
                projecttaskstatus: statusSelect ? statusSelect.value : '',
                projecttaskprogress: progressVal,
                assigned_user_id: ownerSelect ? ownerSelect.value : '',
                description: descEl ? descEl.value : ''
            };
            app.request.post({ data: payload }).then(function (err) {
                if (err) return;
                if (statusEl && statusSelect) statusEl.textContent = statusSelect.value || '--';
                if (currentTask) {
                    currentTask.startdate = payload.startdate;
                    currentTask.enddate = payload.enddate;
                    currentTask.projecttaskstatus = payload.projecttaskstatus;
                    currentTask.projecttaskprogress = payload.projecttaskprogress;
                    currentTask.progress = payload.projecttaskprogress;
                    currentTask.smownerid = payload.assigned_user_id;
                    currentTask.description = payload.description;
                }
                if (app.helper && app.helper.showSuccessNotification) {
                    app.helper.showSuccessNotification({ message: 'Task updated.' });
                }
                panel.classList.add('hidden');
            });
        });
    }

    if (commentAddBtn) {
        commentAddBtn.addEventListener('click', function () {
            if (!currentTask || !commentInput || typeof app === 'undefined' || !app.request) return;
            var text = commentInput.value || '';
            if (!text.trim()) return;
            var payload = {
                module: 'ModComments',
                action: 'SaveAjax',
                commentcontent: text,
                related_to: currentTask.recordid
            };
            app.request.post({ data: payload }).then(function (err) {
                if (err) return;
                commentInput.value = '';
                loadTaskComments(currentTask.recordid);
            });
        });
    }
    /* Drag and drop tasks between columns */
    var statusMap = { 'Backlog': 'Open', 'In Progress': 'In Progress', 'Completed': 'Completed' };
    var draggedCard = null;
    var columns = board.querySelectorAll('.board-column');

    board.querySelectorAll('.board-card').forEach(function (card) {
        card.addEventListener('dragstart', function (e) {
            draggedCard = card;
            e.dataTransfer.setData('text/plain', card.getAttribute('data-task') || '{}');
            e.dataTransfer.effectAllowed = 'move';
            card.classList.add('dragging');
        });
        card.addEventListener('dragend', function () {
            card.classList.remove('dragging');
            board.querySelectorAll('.board-column').forEach(function (c) { c.classList.remove('drop-over'); });
            draggedCard = null;
        });
    });

    columns.forEach(function (col) {
        var cardsContainer = col.querySelector('.board-cards');
        if (!cardsContainer) return;
        col.addEventListener('dragenter', function (e) {
            e.preventDefault();
            e.stopPropagation();
            col.classList.add('drop-over');
        });
        col.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.dataTransfer.dropEffect = 'move';
        });
        col.addEventListener('dragleave', function (e) {
            if (!col.contains(e.relatedTarget)) col.classList.remove('drop-over');
        });
        col.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            col.classList.remove('drop-over');
            if (!draggedCard) return;
            var targetStatus = col.getAttribute('data-status');
            var newStatus = cardsContainer.getAttribute('data-status-value') || statusMap[targetStatus] || 'Open';
            var taskData = {};
            try {
                taskData = JSON.parse(draggedCard.getAttribute('data-task') || '{}');
            } catch (_) {}
            if (!taskData.recordid) return;
            cardsContainer.appendChild(draggedCard);
            taskData.projecttaskstatus = newStatus;
            draggedCard.setAttribute('data-task', JSON.stringify(taskData));
            updateColumnCounts();
            if (typeof app !== 'undefined' && app.request) {
                app.request.post({
                    data: {
                        module: 'ProjectTask',
                        action: 'SaveTask',
                        record: taskData.recordid,
                        projecttaskname: taskData.name || '',
                        projectid: taskData.projectid || '',
                        startdate: taskData.startdate || '',
                        enddate: taskData.enddate || '',
                        projecttaskstatus: newStatus,
                        projecttaskprogress: (taskData.progress || '').toString().replace('%', '') || '',
                        assigned_user_id: taskData.smownerid || '',
                        description: taskData.description || ''
                    }
                }).then(function (err) {
                    if (!err && app.helper && app.helper.showSuccessNotification) {
                        app.helper.showSuccessNotification({ message: 'Task moved.' });
                    }
                });
            }
        });
    });

    function updateColumnCounts() {
        board.querySelectorAll('.board-column').forEach(function (col) {
            var cards = col.querySelector('.board-cards');
            var count = cards ? cards.querySelectorAll('.board-card').length : 0;
            var badge = col.querySelector('.status-count');
            if (badge) badge.textContent = count;
        });
    }
})();
</script>
{/strip}
