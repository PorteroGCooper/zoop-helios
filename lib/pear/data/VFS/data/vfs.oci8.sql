-- $Horde: framework/VFS/data/vfs.oci8.sql,v 1.1 2005/04/07 13:24:24 jan Exp $

CREATE TABLE vfs (
    vfs_id        NUMBER(16) NOT NULL,
    vfs_type      NUMBER(8) NOT NULL,
    vfs_path      VARCHAR2(255),
    vfs_name      VARCHAR2(255) NOT NULL,
    vfs_modified  NUMBER(16) NOT NULL,
    vfs_owner     VARCHAR2(255),
    vfs_data      BLOB,
--
    PRIMARY KEY   (vfs_id)
);

CREATE INDEX vfs_path_idx ON vfs (vfs_path);
CREATE INDEX vfs_name_idx ON vfs (vfs_name);
