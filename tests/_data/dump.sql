-- Add test organizations
INSERT INTO organizations (id, name, subdomain) VALUES ('2', 'RollCall', 'rollcall');
INSERT INTO organizations (id, name, subdomain) VALUES ('3', 'Testers', 'testers');
INSERT INTO organizations (id, name, subdomain) VALUES ('4', 'Dummy org', 'dummy');

-- Add test users
INSERT INTO users (id, name, description, password, person_type, invite_token, role, organization_id)
VALUES
('1', 'Test user', 'Test user','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'member', 2),
('2', 'Admin user','Admin user','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'admin', 2),
('3', 'Org member','Org member','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'member', 2),
('4', 'Org owner','Org owner','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'owner', 2),
('5', 'Org admin','Org admin','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'admin', 2),
('6', 'Org member 2','Org Member 2','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'member', 'asupersecrettoken', 'member', 2),
('7', 'Test user', 'Test user','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'admin', 3),
('8', 'Org owner','Org owner','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC', 'user', NULL, 'owner', 3);

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

--Add test contacts
INSERT INTO contacts (id, user_id, preferred, type, contact) VALUES ('1', '1', '1', 'phone', '0721674180');
INSERT INTO contacts (id, user_id, preferred, type, contact, unsubscribe_token) VALUES ('2', '1', '1', 'email', 'test@ushahidi.com', 'testunsubscribetoken');
INSERT INTO contacts (id, user_id, preferred, type, contact) VALUES ('3', '2', '0', 'email', 'linda@ushahidi.com');
INSERT INTO contacts (id, user_id, preferred, type, contact) VALUES ('4', '4', '0', 'phone', '0792999999');
INSERT INTO contacts (id, user_id, preferred, type, contact) VALUES ('5', '2', '1', 'email', 'admin@ushahidi.com');
INSERT INTO contacts (id, user_id, preferred, type, contact) VALUES ('6', '3', '1', 'email', 'org_member@ushahidi.com');
INSERT INTO contacts (id, user_id, preferred, type, contact) VALUES ('7', '4', '1', 'email', 'org_owner@ushahidi.com');
INSERT INTO contacts (id, user_id, preferred, type, contact) VALUES ('8', '5', '1', 'email', 'org_admin@ushahidi.com');
INSERT INTO contacts (id, user_id, preferred, type, contact) VALUES ('9', '6', '1', 'email', 'org_member2@ushahidi.com');
INSERT INTO contacts (id, user_id, preferred, type, contact) VALUES ('10', '1', '1', 'email', 'test+contact2@ushahidi.com');

--Add test roll calls
INSERT INTO roll_calls (id, message, organization_id, status, sent, user_id) VALUES ('1', 'Westgate under siege', '2', 'pending', '0', '4');
INSERT INTO roll_calls (id, message, organization_id, status, sent, user_id) VALUES ('2', 'Another test roll call', '3', 'pending', '0', '1');
INSERT INTO roll_calls (id, message, organization_id, status, sent, user_id) VALUES ('3', 'yet another test roll call', '2', 'pending', '0', '1');
INSERT INTO roll_calls (id, message, organization_id, status, sent, user_id, answers) VALUES ('4', 'Roll call with answers', '2', 'pending', '0', '1', '["yes", "no"]');
INSERT INTO roll_calls (id, message, organization_id, status, sent, user_id, answers) VALUES ('5', 'Roll call with answers', '2', 'pending', '0', '2', '["yes", "no"]');

-- Add test roll call messages
INSERT INTO roll_call_messages (contact_id, roll_call_id) VALUES ('1', '1');
INSERT INTO roll_call_messages (contact_id, roll_call_id) VALUES ('3', '1');
INSERT INTO roll_call_messages (contact_id, roll_call_id) VALUES ('4', '1');
INSERT INTO roll_call_messages (contact_id, roll_call_id) VALUES ('4', '2');
INSERT INTO roll_call_messages (contact_id, roll_call_id) VALUES ('6', '2');
INSERT INTO roll_call_messages (contact_id, roll_call_id) VALUES ('1', '4');
INSERT INTO roll_call_messages (contact_id, roll_call_id) VALUES ('10', '1');

-- Add test roll call recipients
INSERT INTO roll_call_recipients (user_id, roll_call_id, response_status) VALUES ('1', '1', 'replied');
INSERT INTO roll_call_recipients (user_id, roll_call_id) VALUES ('2', '1');
INSERT INTO roll_call_recipients (user_id, roll_call_id, response_status) VALUES ('4', '1', 'unresponsive');
INSERT INTO roll_call_recipients (user_id, roll_call_id) VALUES ('4', '2');
INSERT INTO roll_call_recipients (user_id, roll_call_id, response_status) VALUES ('3', '2', 'waiting');
INSERT INTO roll_call_recipients (user_id, roll_call_id) VALUES ('3', '3');


-- Add test replies
INSERT INTO replies (id, message, contact_id, roll_call_id, user_id, created_at) VALUES ('1', 'I am OK', '1', '1', '1', NOW());
INSERT INTO replies (id, message, contact_id, roll_call_id, user_id, created_at) VALUES ('2', 'Not OK yet', '4', '1', '4', NOW() - INTERVAL 1 DAY);
INSERT INTO replies (id, message, contact_id, roll_call_id, user_id, created_at) VALUES ('3', 'Latest answer', '4', '1', '4', NOW());
INSERT INTO replies (id, message, contact_id, roll_call_id, user_id, created_at) VALUES ('4', 'Not OK again', '6', '3', '4', NOW() - INTERVAL 2 MINUTE);
INSERT INTO replies (id, message, contact_id, roll_call_id, user_id, created_at) VALUES ('5', 'Latest answer again', '6', '3', '4', NOW() - INTERVAL 1 MINUTE);

-- Add test settings
INSERT INTO settings (organization_id, `key`, `values`) VALUES ('2', 'organization_types', '["election"]') ON DUPLICATE KEY UPDATE `values` = '["election"]';
