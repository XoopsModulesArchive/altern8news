#
# Table structure for table `stories`
#

CREATE TABLE atikel (
    storyid      INT(8) UNSIGNED      NOT NULL AUTO_INCREMENT,
    uid          INT(5) UNSIGNED      NOT NULL DEFAULT '0',
    title        VARCHAR(255)         NOT NULL DEFAULT '',
    created      INT(10) UNSIGNED     NOT NULL DEFAULT '0',
    published    INT(10) UNSIGNED     NOT NULL DEFAULT '0',
    expired      INT(10) UNSIGNED     NOT NULL DEFAULT '0',
    hostname     VARCHAR(20)          NOT NULL DEFAULT '',
    nohtml       TINYINT(1)           NOT NULL DEFAULT '0',
    nosmiley     TINYINT(1)           NOT NULL DEFAULT '0',
    hometext     TEXT                 NOT NULL,
    bodytext     TEXT                 NOT NULL,
    counter      INT(8) UNSIGNED      NOT NULL DEFAULT '0',
    topicid      SMALLINT(4) UNSIGNED NOT NULL DEFAULT '1',
    ihome        TINYINT(1)           NOT NULL DEFAULT '0',
    notifypub    TINYINT(1)           NOT NULL DEFAULT '0',
    story_type   VARCHAR(5)           NOT NULL DEFAULT '',
    topicdisplay TINYINT(1)           NOT NULL DEFAULT '0',
    topicalign   CHAR(1)              NOT NULL DEFAULT 'R',
    comments     SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (storyid),
    KEY idxstoriestopic (topicid),
    KEY ihome (ihome),
    KEY uid (uid),
    KEY published_ihome (published, ihome),
    KEY title (title(40)),
    KEY created (created),
    FULLTEXT KEY `search` (`title`, `hometext`, `bodytext`)
)
    ENGINE = ISAM;
# --------------------------------------------------------

#
# Table structure for table `topics`
#

CREATE TABLE temen (
    topic_id     SMALLINT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
    topic_pid    SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
    topic_imgurl VARCHAR(20)          NOT NULL DEFAULT '',
    topic_title  VARCHAR(50)          NOT NULL DEFAULT '',
    PRIMARY KEY (topic_id),
    KEY pid (topic_pid)
)
    ENGINE = ISAM;

INSERT INTO temen
VALUES (1, 0, 'xoops.gif', 'XOOPS');
