-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net


INSERT INTO `skill_category` (`id`, `parent_id`, `name`) VALUES
(1, NULL, 'Programming languages'),
(2, NULL, 'Libraries & Frameworks'),
(3, 2, 'Java'),
(4, 2, 'PHP'),
(5, 2, 'Ruby'),
(6, 2, 'JavaScript'),
(7, 2, 'CSS'),
(8, 2, 'Windows'),
(9, NULL, 'System administration'),
(10, NULL, 'Operating systems'),
(11, NULL, 'Development tools'),
(12, NULL, 'Graphic'),
(13, 12, 'Graphic'),
(14, 12, 'Video'),
(15, NULL, 'Others');


INSERT INTO `skill` (`id`, `skill_category_id`, `name`) VALUES
(1, 1, 'C'),
(2, 1, 'C++'),
(3, 1, 'C#'),
(4, 1, 'Objective-C'),
(5, 1, 'Java'),
(6, 1, 'Python'),
(7, 1, 'Ruby'),
(8, 1, 'Perl'),
(9, 1, 'Prolog'),
(10, 1, 'Lisp'),
(11, 1, 'Visual Basic'),
(12, 1, 'Pascal'),
(13, 1, 'Delphi'),
(14, 1, 'SQL'),
(15, 1, 'MySQL'),
(16, 1, 'PostgreSQL'),
(17, 1, 'XML'),
(18, 1, 'HTML'),
(19, 1, 'HTML5'),
(20, 1, 'CSS'),
(21, 1, 'CSS3'),
(22, 1, 'JavaScript'),
(23, 1, 'AJAX'),
(24, 1, 'PHP'),
(25, 1, 'Matlab'),
(26, 1, 'UML'),
(27, 1, 'Assembler'),
(28, 1, 'Visual'),
(29, 3, 'JSP'),
(30, 3, 'JSF'),
(31, 3, 'Hibernate'),
(32, 3, 'Swing'),
(33, 3, 'EJB'),
(34, 3, 'Cocoon'),
(35, 3, 'Axis'),
(36, 3, 'iBatis'),
(37, 3, 'Vaadin'),
(38, 4, 'Zend'),
(39, 4, 'Nette'),
(40, 4, 'Symfony'),
(41, 4, 'Drupal'),
(42, 4, 'Laravel'),
(43, 5, 'Ruby on Rails'),
(44, 6, 'AngularJS'),
(45, 6, 'Google Web Toolkit'),
(46, 6, 'jQuery'),
(47, 6, 'ExtJS'),
(48, 6, 'Dojo'),
(49, 6, 'qooxdoo'),
(50, 7, 'Bootstrap'),
(51, 7, 'LESS'),
(52, 7, 'SASS'),
(53, 8, 'ASP'),
(54, 8, 'ASP.NET'),
(55, 8, '.NET'),
(56, 9, 'MariaDB'),
(57, 9, 'SQLite'),
(58, 9, 'dBase'),
(59, 9, 'FoxPro'),
(60, 9, 'LibreOffice Basic'),
(61, 9, 'FileMaker Pro'),
(62, 9, 'Oracel Database'),
(63, 9, 'MySQL'),
(64, 9, 'Microsoft SQL Server'),
(65, 9, 'Postgre SQL'),
(66, 9, 'IBM DB2'),
(67, 9, 'SAP'),
(68, 9, 'Teradata'),
(69, 10, 'LAN/WAN'),
(70, 10, 'UNIX/Linux'),
(71, 10, 'Windows Server'),
(72, 10, 'Vmware ESX'),
(73, 10, 'Client/Server'),
(74, 10, 'IBM/Lotus Notes'),
(75, 11, 'Microsoft Visual Studio'),
(76, 11, 'NetBeans'),
(77, 11, 'Eclipse'),
(78, 11, 'Visual C++'),
(79, 11, 'Dev-C++'),
(80, 11, 'PHP Storm'),
(81, 11, 'Zend Studio'),
(82, 11, 'NuSphere PhpED'),
(83, 11, 'SQuirreL'),
(84, 11, 'Oracle SQL Developer'),
(85, 11, 'MySQL Workbench'),
(86, 13, 'Adobe Photoshop'),
(87, 13, 'Adobe Ilustrator'),
(88, 13, 'CorelDraw'),
(89, 13, 'Zoner Photo Studio'),
(90, 13, 'Corel Photo-Paint'),
(91, 14, 'Adobe Premiere Pro'),
(92, 15, 'Shell'),
(93, 15, 'Bash'),
(94, 15, 'GIT'),
(95, 15, 'SVN'),
(96, 15, 'Mantis'),
(97, 15, 'Adobe Flash'),
(98, 15, 'Flex');