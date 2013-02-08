-- -----------------------------------------------------
-- Table `prefix_User`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_User` (
  `user_id` INT NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(100) NOT NULL ,
  `password` VARCHAR(100) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `joined` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `picture` VARCHAR(100) NULL DEFAULT 'pp_na.png' ,
  `activate` VARCHAR(100) NOT NULL ,
  `desc` TEXT NULL ,
  `deleted` TINYINT NULL DEFAULT 0 ,
  PRIMARY KEY (`user_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_UserMsg`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_UserMsg` (
  `msg_id` INT NOT NULL AUTO_INCREMENT ,
  `from_u` INT NOT NULL ,
  `to_u` INT NOT NULL ,
  `header` VARCHAR(40) NOT NULL ,
  `msg` TEXT NOT NULL ,
  `unread` TINYINT(1)  NULL DEFAULT true ,
  `deleted` TINYINT(1)  NULL DEFAULT false ,
  `sent` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`msg_id`) ,
  INDEX `user_from` (`from_u` ASC) ,
  INDEX `user_to` (`to_u` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_UserFollowUser`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_UserFollowUser` (
  `following` INT NOT NULL ,
  `follower` INT NOT NULL ,
  `since` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  INDEX `follower` (`follower` ASC) ,
  INDEX `following` (`following` ASC) ,
  PRIMARY KEY (`following`, `follower`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_Group`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_Group` (
  `group_id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `desc` TEXT NOT NULL ,
  `public` TINYINT NOT NULL ,
  PRIMARY KEY (`group_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_UserInGroup`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_UserInGroup` (
  `user_id` INT NOT NULL ,
  `group_id` INT NOT NULL ,
  `joined` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `role` ENUM('Owner','Administrator','Member','Invited') NOT NULL DEFAULT 'Invited' ,
  `public` TINYINT(1)  NULL DEFAULT false ,
  `open` TINYINT(1)  NULL DEFAULT false ,
  PRIMARY KEY (`user_id`, `group_id`) ,
  INDEX `user` (`user_id` ASC) ,
  INDEX `group` (`group_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_Application`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_Application` (
  `app_id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `desc` TEXT NOT NULL ,
  `public` TINYINT(1)  NOT NULL DEFAULT false COMMENT 'Viewable for the users in the groups linked to the application, but not the rest. Like in BETA' ,
  `dependencies` TEXT NULL ,
  PRIMARY KEY (`app_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_Platform`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_Platform` (
  `platform_id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`platform_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ApplicationUpdates`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ApplicationUpdates` (
  `update_id` INT NOT NULL AUTO_INCREMENT ,
  `app_id` INT NOT NULL ,
  `platform` INT NOT NULL COMMENT 'Changes may not happend on all platforms at the same time.' ,
  `released` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `changelog` TEXT NOT NULL ,
  `url` TEXT NOT NULL ,
  `revision_nr` VARCHAR(45) NOT NULL ,
  `min_v` VARCHAR(45) NULL ,
  `public` TINYINT(1)  NOT NULL ,
  PRIMARY KEY (`update_id`, `app_id`) ,
  INDEX `app` (`app_id` ASC) ,
  INDEX `platform` (`platform` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_UserApplication`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_UserApplication` (
  `user_id` INT NOT NULL ,
  `app_id` INT NOT NULL ,
  PRIMARY KEY (`user_id`, `app_id`) ,
  INDEX `user` (`user_id` ASC) ,
  INDEX `application` (`app_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ApplicationFeedback`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ApplicationFeedback` (
  `app_id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `recommend` TINYINT(1)  NOT NULL ,
  `message` TEXT NOT NULL ,
  `time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  INDEX `application` (`app_id` ASC) ,
  INDEX `user` (`user_id` ASC) ,
  PRIMARY KEY (`app_id`, `user_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_UserFollowApplication`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_UserFollowApplication` (
  `follower` INT NOT NULL ,
  `app_id` INT NOT NULL ,
  `since` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`follower`, `app_id`) ,
  INDEX `application` (`app_id` ASC) ,
  INDEX `user` (`follower` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_UserDevice`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_UserDevice` (
  `user_device_id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NOT NULL ,
  `name` VARCHAR(100) NULL ,
  `serialnumber` VARCHAR(100) NULL ,
  `last_update` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `os_version` VARCHAR(45) NULL ,
  `platform` INT NOT NULL ,
  `manufactor` VARCHAR(80) NULL DEFAULT 'N/A' ,
  `modell` VARCHAR(100) NULL DEFAULT 'N/A' ,
  PRIMARY KEY (`user_device_id`) ,
  INDEX `user` (`user_id` ASC) ,
  INDEX `platform` (`platform` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_UserDeviceInstalledApps`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_UserDeviceInstalledApps` (
  `user_device_id` INT NOT NULL ,
  `app_id` INT NOT NULL ,
  `installed` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `removed` TIMESTAMP NULL ,
  PRIMARY KEY (`user_device_id`, `app_id`) ,
  INDEX `app` (`app_id` ASC) ,
  INDEX `device` (`user_device_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_Tag`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_Tag` (
  `tag_id` INT NOT NULL AUTO_INCREMENT ,
  `tag` VARCHAR(45) NOT NULL ,
  `added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`tag_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ApplicationTag`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ApplicationTag` (
  `app_id` INT NOT NULL ,
  `tag_id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  PRIMARY KEY (`app_id`, `tag_id`, `user_id`) ,
  INDEX `app` (`app_id` ASC) ,
  INDEX `tag` (`tag_id` ASC) ,
  INDEX `user` (`user_id` ASC)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ApplicationBug`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ApplicationBug` (
  `bug_id` INT NOT NULL AUTO_INCREMENT ,
  `app_id` INT NOT NULL ,
  `user_device_id` INT NOT NULL ,
  `status` ENUM('Discovered','Working on','Fixed') NOT NULL DEFAULT 'Discovered' ,
  `desc` TEXT NOT NULL ,
  `timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`bug_id`, `app_id`) ,
  INDEX `update` (`app_id` ASC) ,
  INDEX `user device` (`user_device_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_GroupApplication`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_GroupApplication` (
  `group_id` INT NOT NULL ,
  `app_id` INT NOT NULL ,
  PRIMARY KEY (`group_id`, `app_id`) ,
  INDEX `app` (`app_id` ASC) ,
  INDEX `group` (`group_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ApplicationScreenshots`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ApplicationScreenshots` (
  `screenshot_id` INT NOT NULL ,
  `app_id` INT NOT NULL ,
  `url` TEXT NULL ,
  PRIMARY KEY (`screenshot_id`) ,
  INDEX `app` (`app_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_Category`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_Category` (
  `category_id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `desc` TEXT NULL ,
  PRIMARY KEY (`category_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ApplicationCategory`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ApplicationCategory` (
  `category_id` INT NOT NULL ,
  `app_id` INT NOT NULL ,
  PRIMARY KEY (`category_id`, `app_id`) ,
  INDEX `cate` (`category_id` ASC) ,
  INDEX `app` (`app_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_Forum`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_Forum` (
  `forum_id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(90) NOT NULL ,
  `desc` TEXT NOT NULL ,
  `group` INT NULL ,
  PRIMARY KEY (`forum_id`) ,
  INDEX `group` (`group` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_AppForum`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_AppForum` (
  `forum_id` INT NOT NULL ,
  `app_id` INT NOT NULL ,
  PRIMARY KEY (`forum_id`, `app_id`) ,
  INDEX `app` (`app_id` ASC) ,
  INDEX `forum` (`forum_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ForumPost`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ForumPost` (
  `post_id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NOT NULL ,
  `title` VARCHAR(45) NOT NULL ,
  `message` TEXT NOT NULL ,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`post_id`) ,
  INDEX `user` (`user_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ForumThread`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ForumThread` (
  `thread_id` INT NOT NULL AUTO_INCREMENT ,
  `forum_id` INT NOT NULL ,
  `locked` TINYINT(1) NULL DEFAULT 0 ,
  `sticky` TINYINT(1) NULL DEFAULT 0 ,
  PRIMARY KEY (`thread_id`, `forum_id`) ,
  INDEX `forum` (`forum_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ForumThreadPost`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ForumThreadPost` (
  `thread_id` INT NOT NULL ,
  `post_id` INT NOT NULL ,
  PRIMARY KEY (`thread_id`, `post_id`) ,
  INDEX `post` (`post_id` ASC) ,
  INDEX `thread` (`thread_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_AdminUser`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_AdminUser` (
  `user_id` INT NOT NULL ,
  `owner` TINYINT NULL DEFAULT 0 ,
  PRIMARY KEY (`user_id`) ,
  INDEX `user` (`user_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ReportedApplication`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ReportedApplication` (
  `app_id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `note` TEXT NOT NULL ,
  PRIMARY KEY (`app_id`, `user_id`) ,
  INDEX `user` (`user_id` ASC) ,
  INDEX `app` (`app_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_ReportedPost`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_ReportedPost` (
  `post_id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`post_id`, `user_id`) ,
  INDEX `post_id` (`post_id` ASC) ,
  INDEX `user` (`user_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_AppAnnouncement`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_AppAnnouncement` (
  `announcement_id` INT NOT NULL AUTO_INCREMENT ,
  `app_id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `header` VARCHAR(80) NOT NULL ,
  `body` TEXT NOT NULL ,
  PRIMARY KEY (`announcement_id`) ,
  INDEX `app` (`app_id` ASC) ,
  INDEX `user` (`user_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prefix_AnnouncementComment`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `prefix_AnnouncementComment` (
  `comment_id` INT NOT NULL AUTO_INCREMENT ,
  `announcement_id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `message` TEXT NOT NULL ,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`comment_id`) ,
  INDEX `ann` (`announcement_id` ASC) ,
  INDEX `user` (`user_id` ASC))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Insert a Category as placeholder
-- -----------------------------------------------------
INSERT INTO `prefix_Category` (name, `desc`) VALUES ('General', 'Placeholder');
INSERT INTO `prefix_Platform` (name, description) VALUES ('Android', 'Google mobile device OS.');

-- -----------------------------------------------------
-- Placeholder table for view `prefix_ApplicationView`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `prefix_ApplicationView` (`app_id` INT, `name` INT, `desc` INT, `released` INT, `platform` INT, `platform_id` INT, `category` INT, `update_id` INT);

-- -----------------------------------------------------
-- View `prefix_ApplicationView`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `prefix_ApplicationView`;
CREATE  OR REPLACE VIEW `prefix_ApplicationView` AS
SELECT A.`app_id` , A.`name` , A.`desc` , MAX( U.`released` ) AS released, P.`name` AS platform, P.`platform_id`, C.`category_id` AS category, MAX( U.`update_id` ) AS update_id
FROM prefix_Application A, prefix_ApplicationUpdates U, prefix_Platform P, prefix_Category C, prefix_ApplicationCategory CU
WHERE U.`app_id` = A.`app_id` 
AND CU.`app_id` = A.`app_id` 
AND C.`category_id` = CU.`category_id` 
AND P.`platform_id` = U.`platform` 
AND A.`public` = 1
AND U.`public` = 1
GROUP BY U.`app_id` ,  `platform` ;

