-- WLA init SQL (CREATE TABLE and sample insert)
-- NOTE: you can run the Setup controller (/setup) to create the table and seed a default admin.

CREATE TABLE `user` (
  `nip` varchar(6) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'pegawai',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`nip`)
);

-- To create a seeded admin you can either run the Setup controller
-- or insert manually using a bcrypt hash for the password.
-- Example (replace <bcrypt_hash> with actual value):
-- INSERT INTO `user` (`nip`,`nama`,`password`,`role`,`created_at`) VALUES ('123456','Admin MSDI','<bcrypt_hash>','admin',NOW());
