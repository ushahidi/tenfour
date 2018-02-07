-- Add test organizations
INSERT INTO organizations (id, name, subdomain) VALUES ('2', 'TenFourTest', 'tenfourtest');
INSERT INTO organizations (id, name, subdomain) VALUES ('3', 'Testers', 'testers');
INSERT INTO organizations (id, name, subdomain) VALUES ('4', 'Dummy org', 'dummy');

INSERT INTO subscriptions (organization_id, subscription_id, customer_id, status, plan_id, quantity, card_type, trial_ends_at, last_four) VALUES ('2', 'sub1', 'cust1', 'active', 'standard-plan', 10, 'visa', '2016-10-30 12:05:01', '1234');
INSERT INTO addons (subscription_id, name, addon_id, quantity) VALUES (1, "extra-credits", "extra-credits", 1000);
INSERT INTO credit_adjustments (organization_id, adjustment, balance) VALUES (2, 3, 3);

-- Add test users
INSERT INTO users (id, name, description, password, person_type, invite_token, role, organization_id)
VALUES
('1', 'Test user', 'Test user','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'responder', 2),
('2', 'Admin user','Admin user','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'admin', 2),
('3', 'Org member','Org member','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'responder', 2),
('4', 'Org owner','Org owner','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'owner', 2),
('5', 'Org admin','Org admin','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'admin', 2),
('6', 'Org member 2','Org Member 2','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'member', 'asupersecrettoken', 'responder', 2),
('7', 'Test user', 'Test user','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'admin', 3),
('8', 'Org owner','Org owner','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'owner', 3),
('9', 'SMS member','SMS member','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'responder', 2),
('10', 'SMS member','SMS member','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'responder', 2),
('11', 'Author','Author role','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'author', 2),
('12', 'Viewer','Viewer role','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'viewer', 2);

-- Add OAuth tokens and scopes
-- Clients
INSERT INTO oauth_clients (id, secret, name) VALUES ('webapp', 'secret', 'webapp');
-- Scopes
INSERT INTO oauth_scopes (id, description) VALUES ('user', 'user'),('organization', 'organization'),('contact', 'contact');

-- Client credentials
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('anonusertoken', 'user');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp', 'client', 'webapp');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('1', 'user');
INSERT INTO oauth_access_tokens VALUES ('anonusertoken',1,1856429714,'2016-10-30 12:05:01','2016-10-30 12:05:01');

-- Password grants
-- User
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('usertoken', 'user'), ('usertoken','contact');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','1');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('2','user'),('6', 'contact');
INSERT INTO oauth_access_tokens VALUES ('usertoken',2,1856429714,'2016-10-30 12:05:01','2016-10-30 12:05:01');

-- Admin
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('admintoken', 'user');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','2');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('3','user');
INSERT INTO oauth_access_tokens VALUES ('admintoken',3,1856429714,'2016-10-30 12:05:01','2016-10-30 12:05:01');

-- Organization Owner
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('orgownertoken', 'organization');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','4');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('4', 'organization');
INSERT INTO oauth_access_tokens VALUES ('orgownertoken',4,1856429714,'2016-10-30 12:05:01','2016-10-30 12:05:01');

-- Organization Admin
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('orgadmintoken', 'organization');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','5');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('5', 'organization');
INSERT INTO oauth_access_tokens VALUES ('orgadmintoken',5,1856429714,'2016-10-30 12:05:01','2016-10-30 12:05:01');

--  Author
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('authortoken', 'organization');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','11');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('6', 'organization');
INSERT INTO oauth_access_tokens VALUES ('authortoken',6,1856429714,'2016-10-30 12:05:01','2016-10-30 12:05:01');

--  Viewer
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('viewertoken', 'organization');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','12');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('7', 'organization');
INSERT INTO oauth_access_tokens VALUES ('viewertoken',7,1856429714,'2016-10-30 12:05:01','2016-10-30 12:05:01');

--Add test contacts
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('1', '1', '2', '1', 'phone', '+254721674180');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact, unsubscribe_token) VALUES ('2', '1', '2', '1', 'email', 'test@ushahidi.com', 'testunsubscribetoken');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('3', '2', '2', '0', 'email', 'linda@ushahidi.com');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('4', '4', '2', '0', 'phone', '+254792999999');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('5', '2', '2', '1', 'email', 'admin@ushahidi.com');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('6', '3', '2', '1', 'email', 'org_member@ushahidi.com');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('7', '4', '2', '1', 'email', 'org_owner@ushahidi.com');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('8', '5', '2', '1', 'email', 'org_admin@ushahidi.com');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('9', '6', '2', '1', 'email', 'org_member2@ushahidi.com');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('10', '1', '2', '1', 'email', 'test+contact2@ushahidi.com');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('11', '7', '3', '1', 'email', 'test+contact2@organization2.com');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('12', '9', '2', '1', 'phone', '254722123456');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('13', '7', '3', '1', 'phone', '+254721674180');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('14', '10', '2', '1', 'phone', '+254722123457');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('15', '5', '2', '1', 'phone', '+254721674200');
INSERT INTO contacts (id, user_id, organization_id, preferred, type, contact) VALUES ('16', '11', '2', '1', 'email', 'test+author@organization2.com');

--Add test check-ins
INSERT INTO check_ins (id, message, organization_id, status, sent, user_id) VALUES ('1', 'Westgate under siege', '2', 'pending', '0', '4');
INSERT INTO check_ins (id, message, organization_id, status, sent, user_id) VALUES ('2', 'Another test check-in', '3', 'pending', '0', '1');
INSERT INTO check_ins (id, message, organization_id, status, sent, user_id) VALUES ('3', 'yet another test check-in', '2', 'pending', '0', '1');
INSERT INTO check_ins (id, message, organization_id, status, sent, user_id, answers) VALUES ('4', 'check-in with answers', '2', 'pending', '0', '1', '[{"answer":"No","color":"#BC6969","icon":"icon-exclaim","type":"custom"},{"answer":"Yes","color":"#E8C440","icon":"icon-check","type":"custom"}]');
INSERT INTO check_ins (id, message, organization_id, status, sent, user_id, answers) VALUES ('5', 'check-in with answers', '2', 'pending', '0', '2', '[{"answer":"No","color":"#BC6969","icon":"icon-exclaim","type":"custom"},{"answer":"Yes","color":"#E8C440","icon":"icon-check","type":"custom"}]');
INSERT INTO check_ins (id, message, organization_id, status, sent, user_id, answers, self_test_check_in) VALUES ('6', 'Did you receive this test check-in?', '2', 'pending', '0', '1', '[{"answer":"Confirmed","color":"#BC6969","icon":"icon-check","type":"custom"}]', 1);
INSERT INTO check_ins (id, message, organization_id, status, sent, user_id, answers, self_test_check_in) VALUES ('7', 'Did you receive this test check-in?', '2', 'pending', '0', '2', '[{"answer":"Confirmed","color":"#BC6969","icon":"icon-check","type":"custom"}]', 1);

-- Add test check-in messages
INSERT INTO check_in_messages (contact_id, check_in_id) VALUES ('1', '1');
INSERT INTO check_in_messages (contact_id, check_in_id) VALUES ('3', '1');
INSERT INTO check_in_messages (contact_id, check_in_id, `from`) VALUES ('4', '1', '20880');
INSERT INTO check_in_messages (contact_id, check_in_id, `from`) VALUES ('4', '2', '20881');
INSERT INTO check_in_messages (contact_id, check_in_id) VALUES ('6', '2');
INSERT INTO check_in_messages (contact_id, check_in_id) VALUES ('1', '4');
INSERT INTO check_in_messages (contact_id, check_in_id) VALUES ('10', '1');
INSERT INTO check_in_messages (contact_id, check_in_id) VALUES ('2', '6');

-- Add test check-in recipients
INSERT INTO check_in_recipients (user_id, check_in_id, response_status) VALUES ('1', '1', 'replied');
INSERT INTO check_in_recipients (user_id, check_in_id, reply_token) VALUES ('2', '1', 'testtoken1');
INSERT INTO check_in_recipients (user_id, check_in_id, response_status) VALUES ('4', '1', 'unresponsive');
INSERT INTO check_in_recipients (user_id, check_in_id) VALUES ('4', '2');
INSERT INTO check_in_recipients (user_id, check_in_id, response_status) VALUES ('3', '2', 'waiting');
INSERT INTO check_in_recipients (user_id, check_in_id, reply_token) VALUES ('3', '3', 'testtoken3');
INSERT INTO check_in_recipients (user_id, check_in_id, response_status) VALUES ('1', '6', 'waiting');

-- Add test replies
INSERT INTO replies (id, message, contact_id, check_in_id, user_id, created_at) VALUES ('1', 'I am OK', '1', '1', '1', NOW());
INSERT INTO replies (id, message, contact_id, check_in_id, user_id, created_at) VALUES ('2', 'Not OK yet', '4', '1', '4', NOW() - INTERVAL 1 DAY);
INSERT INTO replies (id, message, contact_id, check_in_id, user_id, created_at) VALUES ('3', 'Latest answer', '4', '1', '4', NOW());
INSERT INTO replies (id, message, contact_id, check_in_id, user_id, created_at) VALUES ('4', 'Not OK again', '6', '3', '4', NOW() - INTERVAL 2 MINUTE);
INSERT INTO replies (id, message, contact_id, check_in_id, user_id, created_at) VALUES ('5', 'Latest answer again', '6', '3', '4', NOW() - INTERVAL 1 MINUTE);

-- Add test settings
INSERT INTO settings (organization_id, `key`, `values`) VALUES ('2', 'organization_types', '["election"]') ON DUPLICATE KEY UPDATE `values` = '["election"]';
INSERT INTO settings (organization_id, `key`, `values`) VALUES ('2', 'channels', '{ "sms": { "enabled": true } , "email": { "enabled": true } }');

-- Add contact file fields
INSERT INTO contact_files (id, organization_id, columns, maps_to, filename)
VALUES
('1', '2', '["name", "role", "phone", "email", "address", "twitter"]', '{"0":"name","2":"phone","3":"email"}', '/contacts/sample.csv');

-- Add test unverified address
INSERT INTO unverified_addresses (id, address, verification_token) VALUES ('1', 'mary@ushahidi.com', 'token');

-- Add test organization groups
INSERT INTO groups (id, name, organization_id) VALUES ('1', 'Test Group 1', '1');
INSERT INTO groups (id, name, organization_id) VALUES ('2', 'Test Group 2', '2');
INSERT INTO groups (id, name, organization_id) VALUES ('3', 'Test Group 3', '1');

-- Add test group members
INSERT INTO group_users (group_id, user_id) VALUES ('1', '2');
INSERT INTO group_users (group_id, user_id) VALUES ('2', '2');
