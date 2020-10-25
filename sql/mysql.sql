#
# `xoops_search`
#

CREATE TABLE `search` (
    `mid`     INT(8)     NOT NULL DEFAULT '0',
    `notshow` TINYINT(1) NOT NULL DEFAULT '0',
    UNIQUE KEY `mid_2` (`mid`)
)
    ENGINE = ISAM;
