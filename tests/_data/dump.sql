-- Add test users
INSERT INTO users (id, name, email, username, password) VALUES ('1', 'Test user','test@ushahidi.com','test@ushahidi.com','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC'), ('2', 'Admin user','admin@ushahidi.com','admin@ushahidi.com','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC'), ('3', 'Org member','org_member@ushahidi.com','org_member@ushahidi.com','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC'), ('4', 'Org owner','org_owner@ushahidi.com','org_owner@ushahidi.com','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC'), ('5', 'Org admin','org_admin@ushahidi.com','org_admin@ushahidi.com','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC'), ('6', 'Org member 2','org_member2@ushahidi.com','org_member2@ushahidi.com','$2y$10$IuqAql1uP05eZ5ZEen3q1.6v4EhGbh6x7hOUsvR1x9FvI8jnbdRlC');

-- Add test user roles
INSERT INTO roles (id, name) VALUES ('1', 'admin');
INSERT INTO roles (id, name) VALUES ('2', 'member');
INSERT INTO roles (id, name) VALUES ('3', 'login');

-- Insert admin role
INSERT INTO role_user (user_id, role_id) VALUES ('1', '2');
INSERT INTO role_user (user_id, role_id) VALUES ('2', '1');

-- Add OAuth tokens and scopes
-- Clients
INSERT INTO oauth_clients (id, secret, name) VALUES ('webapp', 'secret', 'webapp');
-- Scopes
INSERT INTO oauth_scopes (id, description) VALUES ('user', 'user'),('organization', 'organization'),('contact', 'contact');

-- Client credentials
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('anonusertoken', 'user');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp', 'client', 'webapp');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('1', 'user');
INSERT INTO oauth_access_tokens VALUES ('anonusertoken',1,1856429714,'0000-00-00 00:00:00','0000-00-00 00:00:00');

-- Password grants
-- User
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('usertoken', 'user'), ('usertoken','contact');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','1');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('2','user'),('6', 'contact');
INSERT INTO oauth_access_tokens VALUES ('usertoken',2,1856429714,'0000-00-00 00:00:00','0000-00-00 00:00:00');

-- Admin
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('admintoken', 'user');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','2');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('3','user');
INSERT INTO oauth_access_tokens VALUES ('admintoken',3,1856429714,'0000-00-00 00:00:00','0000-00-00 00:00:00');

-- Organization Owner
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('orgownertoken', 'organization');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','4');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('4', 'organization');
INSERT INTO oauth_access_tokens VALUES ('orgownertoken',4,1856429714,'0000-00-00 00:00:00','0000-00-00 00:00:00');

-- Organization Admin
INSERT INTO oauth_access_token_scopes (access_token_id, scope_id) VALUES ('orgadmintoken', 'organization');
INSERT INTO oauth_sessions (client_id, owner_type, owner_id) VALUES ('webapp','user','5');
INSERT INTO oauth_session_scopes (session_id, scope_id) VALUES ('5', 'organization');
INSERT INTO oauth_access_tokens VALUES ('orgadmintoken',5,1856429714,'0000-00-00 00:00:00','0000-00-00 00:00:00');

-- Add test organizations
INSERT INTO organizations (id, name, url) VALUES ('2', 'RollCall', 'rollcall.rollcall.io');
INSERT INTO organizations (id, name, url) VALUES ('3', 'Testers', 'testers.rollcall.io');

-- Add test organization members
INSERT INTO organization_user (organization_id, user_id, role) VALUES ('2', '4', 'owner');
INSERT INTO organization_user (organization_id, user_id, role) VALUES ('2', '5', 'admin');
INSERT INTO organization_user (organization_id, user_id, role) VALUES ('2', '1', 'member');
INSERT INTO organization_user (organization_id, user_id, role) VALUES ('2', '3', 'member');
INSERT INTO organization_user (organization_id, user_id, role) VALUES ('3', '4', 'owner');
INSERT INTO organization_user (organization_id, user_id, role) VALUES ('3', '1', 'admin');

--Add test contacts
INSERT INTO contacts (id, user_id, can_receive, type, contact) VALUES ('1', '1', '1', 'phone', '0721674180');
INSERT INTO contacts (id, user_id, can_receive, type, contact) VALUES ('2', '1', '1', 'email', 'test@ushahidi.com');
INSERT INTO contacts (id, user_id, can_receive, type, contact) VALUES ('3', '2', '0', 'email', 'linda@ushahidi.com');
INSERT INTO contacts (id, user_id, can_receive, type, contact) VALUES ('4', '4', '0', 'phone', '0792999999');

--Add test roll calls
INSERT INTO roll_calls (id, message, organization_id, status, sent) VALUES ('1', 'Westgate under siege', '2', 'pending', '0');

-- Add test contacts
INSERT INTO contact_roll_call (contact_id, roll_call_id) VALUES ('3', '1');
INSERT INTO contact_roll_call (contact_id, roll_call_id) VALUES ('4', '1');

-- Add test replies
INSERT INTO replies (id, message, contact_id, roll_call_id) VALUES ('1', 'I am OK', '1', '1');
INSERT INTO replies (id, message, contact_id, roll_call_id) VALUES ('2', 'I am OK', '4', '1');
