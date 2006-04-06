-- $Horde: framework/VFS/data/muvfs.sql,v 1.1 2005/04/07 13:24:24 jan Exp $

CREATE TABLE muvfs (
    vfs_id        BIGINT NOT NULL,
    vfs_type      SMALLINT NOT NULL,
    vfs_path      VARCHAR(255) NOT NULL,
    vfs_name      VARCHAR(255) NOT NULL,
    vfs_modified  BIGINT NOT NULL,
    vfs_owner     VARCHAR(255) NOT NULL,
    vfs_perms     SMALLINT NOT NULL,
    vfs_data      LONGBLOB,
-- Or, on some DBMS systems:
--  vfs_data      IMAGE,
    PRIMARY KEY   (vfs_id)
);

CREATE INDEX vfs_path_idx ON muvfs (vfs_path);
CREATE INDEX vfs_name_idx ON muvfs (vfs_name);
