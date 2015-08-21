<?php
/**
 * PostgreSQL database schema
 *
 * @author Frank de Lange <openphoto-f@unternet.org>
 */

try
{
  $utilityObj = new Utility;
  $sql = <<<SQL
  CREATE DATABASE `{$this->postgreSqlDb}` WITH OWNER `$this->postgreSqlUser` ENCODING 'UTF8';
SQL;
  $pdo = new PDO(sprintf('%s:host=%s', 'pgsql', $this->postgreSqlHost), $this->postgreSqlUser, $utilityObj->decrypt($this->postgreSqlPassword));
  $pdo->exec($sql);

  /* types */

  if (!postgresql_db_enum_exists("photo_type"))
  	postgresql_base("CREATE TYPE photo_type AS ENUM ('photo');");

  if (!postgresql_db_enum_exists("photo_album_type"))
  	postgresql_base("CREATE TYPE photo_album_type AS ENUM ('photo','album');");

  if (!postgresql_db_enum_exists("share_type"))
  	postgresql_base("CREATE TYPE share_type AS ENUM ('photo','album','photos','video');");

  if (!postgresql_db_enum_exists("rotation_type"))
  	postgresql_base("CREATE TYPE rotation_type AS ENUM ('0','90','180','270');");

  /* tablespaces */

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}action (
    id text NOT NULL,
    owner text NOT NULL,
    actor text,
    app_id text DEFAULT NULL,
    target_id text DEFAULT NULL,
    target_type text DEFAULT NULL,
    email text DEFAULT NULL,
    name text DEFAULT NULL,
    avatar text DEFAULT NULL,
    website text DEFAULT NULL,
    target_url text DEFAULT NULL,
    permalink text DEFAULT NULL,
    type text DEFAULT NULL,
    value text DEFAULT NULL,
    date_posted text DEFAULT NULL,
    status integer DEFAULT NULL,
    PRIMARY KEY(id,owner)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}action"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}activity (
    id text NOT NULL,
    owner text NOT NULL,
    actor text,
    app_id text NOT NULL,
    type text NOT NULL,
    element_id text NOT NULL,
    data text NOT NULL,
    permission text NOT NULL DEFAULT '0',
    date_created integer NOT NULL,
    PRIMARY KEY(id,owner)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}activity"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}admin (
    key text PRIMARY KEY,
    value text NOT NULL
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}admin"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}album (
    id text NOT NULL,
    owner text NOT NULL,
    actor text,
    name text NOT NULL,
    groups text,
    extra text,
    count_public integer NOT NULL DEFAULT '0',
    count_private integer NOT NULL DEFAULT '0',
    date_last_photo_added integer NOT NULL DEFAULT '0',
    PRIMARY KEY(id,owner)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}album"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}albumGroup (
    owner text NOT NULL,
    actor text,
    album text NOT NULL,
    "group" text NOT NULL,
    UNIQUE(owner,album,"group")
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}albumGroup"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}config (
    id text PRIMARY KEY DEFAULT '',
    alias_of text DEFAULT NULL,
    value text NOT NULL
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}config"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}credential (
    id text NOT NULL,
    owner text NOT NULL,
    actor text,
    name text DEFAULT NULL,
    image text,
    client_secret text DEFAULT NULL,
    user_token text DEFAULT NULL,
    user_secret text DEFAULT NULL,
    permissions text DEFAULT NULL,
    verifier text DEFAULT NULL,
    type text NOT NULL,
    status integer DEFAULT '0',
    date_created integer DEFAULT NULL,
    PRIMARY KEY(id,owner)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}credential"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}elementAlbum (
    id serial PRIMARY KEY,
    owner text NOT NULL,
    actor text,
    "type" photo_type NOT NULL,
    element text NOT NULL,
    album text NOT NULL,
    "order" smallint NOT NULL DEFAULT '0',
    active smallint NOT NULL DEFAULT '1',
    UNIQUE(owner,"type",element,album)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}elementAlbum"))
  {
  	postgresql_base($sql);
  	postgresql_base("CREATE INDEX element_album_owner_album_idx ON {$this->postgreSqlTablePrefix}elementAlbum (owner, album);");
  }

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}elementGroup (
    id serial PRIMARY Key,
    owner text NOT NULL,
    actor text,
    "type" photo_album_type NOT NULL,
    element text NOT NULL,
    "group" text NOT NULL,
    active smallint NOT NULL DEFAULT '1',
    UNIQUE(owner,"type",element,"group")
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}elementGroup"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}elementTag (
    id serial PRIMARY KEY,
    owner text NOT NULL,
    actor text,
    "type" photo_type NOT NULL,
    element text NOT NULL DEFAULT 'photo',
    tag text NOT NULL,
    active smallint NOT NULL DEFAULT '1',
    UNIQUE(owner,"type",element,tag)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}elementTag"))
  {
  	postgresql_base($sql);
		postgresql_base("COMMENT ON TABLE {$this->postgreSqlTablePrefix}elementTag IS 'Tag mapping table for photos (and videos in the future)';");
  }


  $sql = <<<SQL
  CREATE TABLE "{$this->postgreSqlTablePrefix}group" (
    id text PRIMARY KEY,
    owner text NOT NULL,
    actor text,
    app_id text DEFAULT NULL,
    name text DEFAULT NULL,
    permission smallint NOT NULL,
    UNIQUE(id,owner)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}group"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}groupMember (
    id serial PRIMARY KEY,
    owner text NOT NULL,
    actor text,
    "group" text NOT NULL,
    email text NOT NULL,
    UNIQUE(owner,"group",email)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}groupMember"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}photo (
    id text NOT NULL,
    owner text NOT NULL,
    actor text,
    app_id text NOT NULL,
    host text DEFAULT NULL,
    title text DEFAULT NULL,
    description text,
    key text DEFAULT NULL,
    hash text DEFAULT NULL,
    size integer DEFAULT NULL,
    width integer DEFAULT NULL,
    height integer DEFAULT NULL,
    rotation rotation_type NOT NULL DEFAULT '0',
    extra text,
    exif text,
    latitude float(6) DEFAULT NULL,
    longitude float(6) DEFAULT NULL,
    views integer DEFAULT NULL,
    status integer DEFAULT NULL,
    permission integer DEFAULT NULL,
    license text DEFAULT NULL,
    date_taken integer DEFAULT NULL,
    date_taken_day integer DEFAULT NULL,
    date_taken_month integer DEFAULT NULL,
    date_taken_year integer DEFAULT NULL,
    date_uploaded integer DEFAULT NULL,
    date_uploaded_day integer DEFAULT NULL,
    date_uploaded_month integer DEFAULT NULL,
    date_uploaded_year integer DEFAULT NULL,
    date_sort_by_day text NOT NULL,
    filename_original text DEFAULT NULL,
    path_original text DEFAULT NULL,
    path_base text DEFAULT NULL,
    albums text,
    groups text,
    tags text,
    active smallint NOT NULL DEFAULT 1,
    UNIQUE(id,owner)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}photo"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}photoVersion (
    id text NOT NULL DEFAULT '',
    owner text NOT NULL,
    actor text,
    key text NOT NULL DEFAULT '',
    path text DEFAULT NULL,
    UNIQUE(id,owner,key)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}photoVersion"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}relationship (
    actor text NOT NULL,
    follows text NOT NULL,
    date_created integer DEFAULT NULL,
    UNIQUE(actor,follows)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}relationship"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}resourceMap (
    id text NOT NULL,
    owner text NOT NULL,
    actor text,
    resource text NOT NULL,
    date_created integer DEFAULT NULL,
    PRIMARY KEY(owner,id)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}resourceMap"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}shareToken (
    id text NOT NULL,
    owner text NOT NULL,
    actor text,
    type share_type NOT NULL,
    data text NOT NULL,
    date_expires integer NOT NULL,
    PRIMARY KEY(owner,id)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}shareToken"))
  {
  	postgresql_base($sql);
		postgresql_base('CREATE INDEX share_token_owner_type_data_idx ON ' . $this->postgreSqlTablePrefix . 'shareToken (owner,"type",data);');
  }

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}tag (
    id text NOT NULL,
    owner text NOT NULL,
    actor text,
    count_public integer NOT NULL DEFAULT '0',
    count_private integer NOT NULL DEFAULT '0',
    extra text,
    UNIQUE(id,owner)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}tag"))
  	postgresql_base($sql);

  $sql = <<<SQL
  CREATE TABLE "{$this->postgreSqlTablePrefix}user" (
    id text PRIMARY KEY,
    password text NOT NULL,
    extra text NOT NULL,
    "timestamp" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP   
  );
SQL;
  if (!postgresql_db_table_exists("$this->postgreSqlTablePrefix}user"))
	{
		postgresql_base($sql);
		postgresql_base("COMMENT ON COLUMN {$this->postgreSqlTablePrefix}user.id IS 'User''s email address';");
	}

  $sql = <<<SQL
  CREATE TABLE {$this->postgreSqlTablePrefix}webhook (
    id text NOT NULL,
    owner text NOT NULL,
    actor text,
    app_id text DEFAULT NULL,
    callback text DEFAULT NULL,
    topic text DEFAULT NULL,
    UNIQUE(id,owner)
  );
SQL;
  if (!postgresql_db_table_exists("{$this->postgreSqlTablePrefix}webhook"))
  	postgresql_base($sql);


	/* views */

  $sql = <<<SQL
	CREATE OR REPLACE VIEW {$this->postgreSqlTablePrefix}album_element_view AS
		SELECT p.*, ea.album AS album, ea.id AS album_id FROM {$this->postgreSqlTablePrefix}photo AS p
			INNER JOIN {$this->postgreSqlTablePrefix}elementAlbum AS ea
			ON p.id=ea.element
			WHERE p.owner = ea.owner;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
	CREATE OR REPLACE VIEW {$this->postgreSqlTablePrefix}tag_element_view AS
		SELECT p.*, et.tag AS tag, et.id AS tag_id FROM {$this->postgreSqlTablePrefix}photo AS p
			INNER JOIN {$this->postgreSqlTablePrefix}elementTag AS et
			ON p.id=et.element
			WHERE p.owner = et.owner;
SQL;
  postgresql_base($sql);


  /* rules */

  $sql = <<<SQL
  DROP RULE IF EXISTS {$this->postgreSqlTablePrefix}group_member_ignore_duplicate_rule ON {$this->postgreSqlTablePrefix}groupMember;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE RULE {$this->postgreSqlTablePrefix}group_member_ignore_duplicate_rule AS
  ON INSERT TO {$this->postgreSqlTablePrefix}groupMember
    WHERE EXISTS (SELECT 1 FROM {$this->postgreSqlTablePrefix}groupMember
                    WHERE (owner,"group",email) = (NEW.owner, NEW."group", NEW.email))
    DO INSTEAD NOTHING;
SQL;
  postgresql_base($sql);


  $sql = <<<SQL
  DROP RULE IF EXISTS {$this->postgreSqlTablePrefix}element_group_ignore_duplicate_rule ON {$this->postgreSqlTablePrefix}elementGroup;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE RULE {$this->postgreSqlTablePrefix}element_group_ignore_duplicate_rule AS
  ON INSERT TO {$this->postgreSqlTablePrefix}elementGroup
    WHERE EXISTS (SELECT 1 FROM {$this->postgreSqlTablePrefix}elementGroup
                    WHERE (owner,type,element,"group") = (NEW.owner, NEW.type, NEW.element, NEW."group"))
    DO INSTEAD NOTHING;
SQL;
  postgresql_base($sql);


   $sql = <<<SQL
  DROP RULE IF EXISTS {$this->postgreSqlTablePrefix}activity_ignore_duplicate_rule ON {$this->postgreSqlTablePrefix}activity;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE RULE {$this->postgreSqlTablePrefix}activity_ignore_duplicate_rule AS
  ON INSERT TO {$this->postgreSqlTablePrefix}activity
    WHERE EXISTS (SELECT 1 FROM {$this->postgreSqlTablePrefix}activity
                    WHERE (id, owner) = (NEW.id, NEW.owner))
    DO INSTEAD NOTHING;
SQL;
  postgresql_base($sql);


  $sql = <<<SQL
  DROP RULE IF EXISTS {$this->postgreSqlTablePrefix}tag_update_duplicate_rule ON {$this->postgreSqlTablePrefix}tag;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE RULE {$this->postgreSqlTablePrefix}tag_update_duplicate_rule AS
  ON INSERT TO {$this->postgreSqlTablePrefix}tag
    WHERE EXISTS (SELECT 1 FROM {$this->postgreSqlTablePrefix}tag
                    WHERE (id, owner) = (NEW.id, NEW.owner))
    DO INSTEAD
      UPDATE {$this->postgreSqlTablePrefix}tag
        SET actor=NEW.actor, count_public=NEW.count_public, count_private=NEW.count_private, extra=NEW.extra
        WHERE (id, owner) = (NEW.id, NEW.owner);
SQL;
  postgresql_base($sql);


 $sql = <<<SQL
  DROP RULE IF EXISTS {$this->postgreSqlTablePrefix}element_tag_ignore_duplicate_rule ON {$this->postgreSqlTablePrefix}elementTag;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE RULE {$this->postgreSqlTablePrefix}element_tag_ignore_duplicate_rule AS
  ON INSERT TO {$this->postgreSqlTablePrefix}elementTag
    WHERE EXISTS (SELECT 1 FROM {$this->postgreSqlTablePrefix}elementTag
                    WHERE (owner, "type", element, tag) = (NEW.owner, NEW."type", NEW.element, NEW.tag))
    DO INSTEAD NOTHING;
SQL;
  postgresql_base($sql);


  $sql = <<<SQL
  DROP RULE IF EXISTS {$this->postgreSqlTablePrefix}element_album_ignore_duplicate_rule ON {$this->postgreSqlTablePrefix}elementAlbum;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE RULE {$this->postgreSqlTablePrefix}element_album_ignore_duplicate_rule AS
  ON INSERT TO {$this->postgreSqlTablePrefix}elementAlbum
    WHERE EXISTS (SELECT 1 FROM {$this->postgreSqlTablePrefix}elementAlbum
                    WHERE (owner, "type", element, album) = (NEW.owner, NEW."type", NEW.element, NEW.album))
    DO INSTEAD NOTHING;
SQL;
  postgresql_base($sql);


  $sql = <<<SQL
  DROP RULE IF EXISTS {$this->postgreSqlTablePrefix}photo_version_ignore_duplicate_rule ON {$this->postgreSqlTablePrefix}photoVersion;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE RULE {$this->postgreSqlTablePrefix}photo_version_ignore_duplicate_rule AS
  ON INSERT TO {$this->postgreSqlTablePrefix}photoVersion
    WHERE EXISTS (SELECT 1 FROM {$this->postgreSqlTablePrefix}photoVersion
                    WHERE (id, owner, key) = (NEW.id, NEW.owner, NEW.key))
    DO INSTEAD NOTHING;
SQL;
  postgresql_base($sql);



  /* functions */

  $sql = <<<SQL
  CREATE OR REPLACE FUNCTION update_public_private_count() RETURNS trigger AS $$
  DECLARE v_pub text; v_priv text; v_owner text; v_item text;
  BEGIN

    CASE TG_OP
      WHEN 'INSERT','UPDATE' THEN
        v_owner := NEW.owner;
        CASE TG_TABLE_NAME
          WHEN '{$this->postgreSqlTablePrefix}elementalbum' THEN
            v_item := NEW.album;
          WHEN '{$this->postgreSqlTablePrefix}elementtag' THEN
            v_item := NEW.tag;
          ELSE
          END CASE;
      WHEN 'DELETE' THEN
        v_owner := OLD.owner;
        CASE TG_TABLE_NAME
          WHEN '{$this->postgreSqlTablePrefix}elementalbum' THEN
            v_item := OLD.album;
          WHEN '{$this->postgreSqlTablePrefix}elementtag' THEN
            v_item := OLD.tag;
          ELSE
          END CASE;
      ELSE
    END CASE;

    CASE TG_TABLE_NAME
      WHEN '{$this->postgreSqlTablePrefix}elementalbum' THEN
        EXECUTE 'SELECT COUNT(*) FROM {$this->postgreSqlTablePrefix}album_element_view WHERE owner=$1 AND album=$2 AND permission=''1'' AND active=''1''' INTO v_pub USING v_owner, v_item;
        EXECUTE 'SELECT COUNT(*) FROM {$this->postgreSqlTablePrefix}album_element_view WHERE owner=$1 AND album=$2 AND active=''1''' INTO v_priv USING v_owner, v_item;
        EXECUTE 'UPDATE {$this->postgreSqlTablePrefix}album SET count_public=$1::int, count_private=$2::int WHERE owner=$3 AND id=$4' USING v_pub, v_priv, v_owner, v_item;
      WHEN '{$this->postgreSqlTablePrefix}elementtag' THEN
        EXECUTE 'SELECT COUNT(*) FROM {$this->postgreSqlTablePrefix}tag_element_view WHERE owner=$1 AND tag=$2 AND permission=''1'' AND active=''1''' INTO v_pub USING v_owner, v_item;
        EXECUTE 'SELECT COUNT(*) FROM {$this->postgreSqlTablePrefix}tag_element_view WHERE owner=$1 AND tag=$2 AND active=''1''' INTO v_priv USING v_owner, v_item;
        EXECUTE 'UPDATE {$this->postgreSqlTablePrefix}tag SET count_public=$1::int, count_private=$2::int WHERE owner=$3 AND id=$4' USING v_pub, v_priv, v_owner, v_item;
      ELSE
    END CASE;

    CASE TG_OP
      WHEN 'INSERT','UPDATE' THEN
        RETURN NEW;
      WHEN 'DELETE' THEN
        RETURN OLD;
      ELSE
        RETURN NULL;
    END CASE;

  END;
  $$ LANGUAGE plpgsql;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
	CREATE OR REPLACE FUNCTION update_active_on_element_update() RETURNS trigger AS $$
  DECLARE v_element text; v_owner text; v_active smallint;
	BEGIN

		v_element := NEW.id;
 		v_owner := NEW.owner;
  	v_active := NEW.active;

		CASE TG_TABLE_NAME
			WHEN '{$this->postgreSqlTablePrefix}photo' THEN
				EXECUTE 'UPDATE {$this->postgreSqlTablePrefix}elementtag AS et SET active=$1 WHERE et.owner = $2 AND et.type = ''photo'' AND et.element = $3' USING v_active, v_owner, v_element;
				EXECUTE 'UPDATE {$this->postgreSqlTablePrefix}elementalbum AS ea SET active=$1 WHERE ea.owner = $2  AND ea.type = ''photo'' AND ea.element = $3' USING v_active, v_owner, v_element;
			ELSE
		END CASE;

		RETURN NULL;
	END;
	$$ LANGUAGE plpgsql;
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE OR REPLACE FUNCTION delete_on_element_delete() RETURNS trigger AS $$
  DECLARE v_id text; v_owner text;
  BEGIN

    v_owner := OLD.owner;
    v_id := OLD.id;

    CASE TG_TABLE_NAME
      WHEN '{$this->postgreSqlTablePrefix}album' THEN
        EXECUTE 'DELETE FROM {$this->postgreSqlTablePrefix}elementalbum AS ea WHERE ea.owner = $1  AND ea.album = $2' USING v_owner, v_id;
      WHEN '{$this->postgreSqlTablePrefix}tag' THEN
        EXECUTE 'DELETE FROM {$this->postgreSqlTablePrefix}elementtag AS et WHERE et.owner = $1  AND et.tag = $2' USING v_owner, v_id;
      ELSE
    END CASE;

    RETURN NULL;
  END;
  $$ LANGUAGE plpgsql;
SQL;
  postgresql_base($sql);


  /* triggers */

  $sql = <<<SQL
  CREATE TRIGGER update_album_counts_on_delete AFTER DELETE ON {$this->postgreSqlTablePrefix}elementAlbum
  FOR EACH ROW EXECUTE PROCEDURE update_public_private_count();
SQL;
  postgresql_base($sql);
  $sql = <<<SQL
  CREATE TRIGGER update_album_counts_on_insert AFTER INSERT ON {$this->postgreSqlTablePrefix}elementAlbum
  FOR EACH ROW EXECUTE PROCEDURE update_public_private_count();
SQL;
  postgresql_base($sql);
  $sql = <<<SQL
  CREATE TRIGGER update_album_counts_on_update AFTER UPDATE ON {$this->postgreSqlTablePrefix}elementAlbum
  FOR EACH ROW EXECUTE PROCEDURE update_public_private_count();
SQL;
  postgresql_base($sql);
  $sql = <<<SQL
  CREATE TRIGGER update_tag_counts_on_delete AFTER DELETE ON {$this->postgreSqlTablePrefix}elementTag
  FOR EACH ROW EXECUTE PROCEDURE update_public_private_count();
SQL;
  postgresql_base($sql);
  $sql = <<<SQL
  CREATE TRIGGER update_tag_counts_on_insert AFTER INSERT ON {$this->postgreSqlTablePrefix}elementTag
  FOR EACH ROW EXECUTE PROCEDURE update_public_private_count();
SQL;
  postgresql_base($sql);
  $sql = <<<SQL
  CREATE TRIGGER update_tag_counts_on_update AFTER UPDATE ON {$this->postgreSqlTablePrefix}elementTag
  FOR EACH ROW EXECUTE PROCEDURE update_public_private_count();
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE TRIGGER update_active_on_photo_update AFTER UPDATE ON {$this->postgreSqlTablePrefix}photo
  FOR EACH ROW EXECUTE PROCEDURE update_active_on_element_update();
SQL;
  postgresql_base($sql);

  $sql = <<<SQL
  CREATE TRIGGER delete_element_album_on_album_delete BEFORE DELETE ON {$this->postgreSqlTablePrefix}album
  FOR EACH ROW EXECUTE PROCEDURE delete_on_element_delete();
SQL;
  postgresql_base($sql);
  $sql = <<<SQL
  CREATE TRIGGER delete_element_tag_on_tag_delete BEFORE DELETE ON {$this->postgreSqlTablePrefix}tag
  FOR EACH ROW EXECUTE PROCEDURE delete_on_element_delete();
SQL;
  postgresql_base($sql);

  /* version */

  $sql = <<<SQL
    INSERT INTO {$this->postgreSqlTablePrefix}admin (key,value) 
    VALUES (:key, :value)
SQL;
  postgresql_base($sql, array(':key' => 'version', ':value' => '4.0.2'));

  return true;
}
catch(Exception $e)
{
  getLogger()->crit($e->getMessage());
  return false;
}

function postgresql_base($sql, $params = array())
{
  try
  {
    getDatabase()->execute($sql, $params);
    getLogger()->info($sql);
  }
  catch(Exception $e)
  {
    getLogger()->crit($e->getMessage()); 
    throw $e;
  }
}

function postgresql_db_table_exists($table) {
    $table = strtolower($table);
    $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = '$table'";
    $params = array();
    $result = getDatabase()->one($sql, $params);
    getLogger()->info("$table");
    getLogger()->info($result['count']);
    return (bool) $result['count'];
}

function postgresql_db_enum_exists($label) {
    $label = strtolower($label);
    $sql = "SELECT COUNT(*) FROM pg_type WHERE typname = '$label'";
    $params = array();
    $result = getDatabase()->one($sql, $params);
    return (bool) $result['count'];
}

