-- Sample Admin User (password: Admin123!)
INSERT INTO users (name, email, password, phone, role, status)
VALUES (
  'Admin User',
  'admin@consultease.com',
  '$2y$10$UO.hUzl.oXIbJvM1zpMgu.nDbePUzAii3b4kUP59tvhHDyx1fCKWG',
  '0500000000',
  'admin',
  'active'
);

-- Sample Clients (passwords: Client123!, Client456!)
INSERT INTO users (name, email, password, phone, role, status)
VALUES
  ('Esi Mensah', 'esi.cl@client.com', '$2y$10$MZ0KlEwK.JYqXwJ0bI6yuOXYECGbG/nCi69Wvq8AozZtl5ELfqHpS', '0501112233', 'client', 'active'),
  ('Kwame Boateng', 'kwame.b@client.com', '$2y$10$YQxQwN8uW/c69gN1WhkU7.TUaM30wz6w5ph1b32B9KG6rDfbz2TCu', '0505566778', 'client', 'active');

-- Sample Expertise Categories
INSERT INTO expertise (name, description)
VALUES
  ('Business Development', 'Business growth, planning and sustainability.'),
  ('Marketing Strategy', 'Modern and digital marketing techniques.'),
  ('Operations Management', 'Optimization of business operations.');

-- Sample Consultants (passwords: Consultant1!, Consultant2!, Consultant3!)
INSERT INTO users (name, email, password, phone, role, status)
VALUES
  ('Abena Koomson', 'abena@consult.com', '$2y$10$fE0ZzLowSsr6v6e7jqTQdOIq5M0TgJwko1CewN0DjpKv7Qd34Oj7u', '0503234567', 'consultant', 'active'),
  ('Kwesi Sarkodie', 'kwesi@consult.com', '$2y$10$Drd3Nn7aUaZ9byXQBwh66Oj6x8vMFuD4I1Yo6.Ui9DdBet7Pv6dau', '0509988773', 'consultant', 'active'),
  ('Ama Osei', 'ama@consult.com', '$2y$10$OE/Cdki5Nfblm.uZw1tD4OAeqDaclkWaeeypA5OGRZtkA8tUQKgF6', '0501234433', 'consultant', 'active');

-- Map Consultants to Expertise
-- (Get user_id values for above from auto-increment sequence!)
INSERT INTO consultants (user_id, bio, years_of_experience, expertise_id, profile_status)
VALUES
  (4, 'Business dev specialist with 6 years experience.', 6, 1, 'approved'),
  (5, 'Expert in digital marketing campaigns. 8 years.', 8, 2, 'approved'),
  (6, 'Process and operations consultant, 5 years.', 5, 3, 'approved');

-- Admin Permissions Sample
INSERT INTO admin (user_id, permissions)
VALUES (1, '{"manage_users": true, "manage_appointments": true, "settings": true}');