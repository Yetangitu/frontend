<?php

$status = true;

/* action */
$table = 'action';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `id`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* credential */
$table = 'credential';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `id`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* elementGroup */
$table = 'elementGroup';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `owner`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* elementTag */
$table = 'elementTag';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `id`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* group */
$table = 'group';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `id`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* groupMember */
$table = 'groupMember';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `owner`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `email` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* photo */
$table = 'photo';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `id`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* photoVersion */
$table = 'photoVersion';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `id`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* tag */
$table = 'tag';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `id`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `id` VARCHAR(127);
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* user */
$table = 'user';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP PRIMARY KEY;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `id` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

/* webhook */
$table = 'webhook';
$sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` DROP KEY `id`;
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `id` VARCHAR(127);
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` UPDATE `owner` VARCHAR(127);
SQL;
$status = $status && mysql_2_0_1($sql);

$sql = <<<SQL
  UPDATE `{$this->mySqlTablePrefix}admin` SET `value`=:version WHERE `key`=:key;
SQL;
$status = $status && mysql_2_0_1($sql, array(':key' => 'version', ':version' => '2.0.1'));

// #649
$sql = <<<SQL
  ALTER TABLE `{$this->mySqlTablePrefix}photo` ADD INDEX ( `owner` ) ;
SQL;
$status = $status && mysql_2_0_1($sql);

// date_sort_by_day
$sql = <<<SQL
  ALTER TABLE `{$this->mySqlTablePrefix}photo` ADD `date_sort_by_day` VARCHAR( 14 ) NOT NULL AFTER `date_uploadedYear`;
SQL;
$status = $status && mysql_2_0_1($sql);

$sql = <<<SQL
  UPDATE `{$this->mySqlTablePrefix}photo` SET date_sort_by_day= CONCAT(
    CAST(date_taken_year AS CHAR),
    LPAD(CAST(date_taken_month AS CHAR),2,"0"),
    LPAD(CAST(date_taken_day AS CHAR),2,"0"),
    LPAD(23-HOUR(FROM_UNIXTIME(date_taken)),2,"0"),
    LPAD(59-MINUTE(FROM_UNIXTIME(date_taken)),2,"0"),
    LPAD(59-SECOND(FROM_UNIXTIME(date_taken)),2,"0")
  );
SQL;
$status = $status && mysql_2_0_1($sql);


function mysql_2_0_1($sql, $params = array())
{
  try
  {
    getDatabase()->execute($sql, $params);
    getLogger()->info($sql);
  }
  catch(Exception $e)
  {
    getLogger()->crit($e->getMessage()); 
    return false;
  }
  return true;
}

return $status;
