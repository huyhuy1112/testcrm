-- SQL script to register AccountsHandler and ContactsHandler
-- Run this in phpMyAdmin or MySQL client

-- Register AccountsHandler
INSERT INTO vtiger_eventhandlers 
(eventhandler_id, event_name, handler_path, handler_class, cond, is_active, dependent_on)
SELECT 
    (SELECT COALESCE(MAX(eventhandler_id), 0) + 1 FROM vtiger_eventhandlers),
    'vtiger.entity.aftersave.final',
    'modules/Accounts/AccountsHandler.php',
    'AccountsHandler',
    '',
    1,
    '[]'
WHERE NOT EXISTS (
    SELECT 1 FROM vtiger_eventhandlers 
    WHERE handler_class = 'AccountsHandler'
);

-- Register ContactsHandler
INSERT INTO vtiger_eventhandlers 
(eventhandler_id, event_name, handler_path, handler_class, cond, is_active, dependent_on)
SELECT 
    (SELECT COALESCE(MAX(eventhandler_id), 0) + 1 FROM vtiger_eventhandlers),
    'vtiger.entity.aftersave.final',
    'modules/Contacts/ContactsHandler.php',
    'ContactsHandler',
    '',
    1,
    '[]'
WHERE NOT EXISTS (
    SELECT 1 FROM vtiger_eventhandlers 
    WHERE handler_class = 'ContactsHandler'
);

-- Activate both handlers if they exist but are inactive
UPDATE vtiger_eventhandlers 
SET is_active = 1 
WHERE handler_class IN ('AccountsHandler', 'ContactsHandler') 
  AND is_active = 0;

-- Verify registration
SELECT 
    handler_class,
    event_name,
    handler_path,
    is_active,
    eventhandler_id
FROM vtiger_eventhandlers 
WHERE handler_class IN ('AccountsHandler', 'ContactsHandler')
ORDER BY handler_class;

