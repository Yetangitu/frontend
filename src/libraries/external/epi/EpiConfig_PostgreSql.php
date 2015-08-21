<?php
class EpiConfig_PostgreSql extends EpiConfig
{
  private $db, $table;
  public function __construct($params)
  {
    parent::__construct();
    $this->db = EpiDatabase::getInstance('pgsql', $params['database'], $params['host'], $params['username'], $params['password']);
    $this->table = $params['table'];
    if(isset($params['cacheMask']) && $params['cacheMask'])
    {
      $this->cacheMask = $params['cacheMask'];
      $this->cacheObj = getCache();
    }
  }

  public function getString($file)
  {
    $res = $this->getRecord($file);
    return $res['value'];
  }

  public function exists($file)
  {
    if($file == '')
    {
      EpiException::raise(new EpiConfigException("Configuration file cannot be empty when calling exists"));
      return; // need to simulate same behavior if exceptions are turned off
    }

    $file = $this->getFilePath($file);
    $res = $this->db->one("SELECT * FROM {$this->table} WHERE id=:file OR alias_of=:alias_of", array(':file' => $file, ':alias_of' => $file));
    return $res !== false;
  }

  public function isAlias($file)
  {
    if($file == '')
    {
      EpiException::raise(new EpiConfigException("Configuration file cannot be empty when calling isAlias"));
      return; // need to simulate same behavior if exceptions are turned off
    }

    $file = $this->getFilePath($file);
    $res = $this->db->one("SELECT * FROM {$this->table} WHERE id=:file OR alias_of=:alias_of", array(':file' => $file, ':alias_of' => $file));
    if($res === false)
      return null;

    return $file == $res['alias_of'];
  }

  public function load(/*$file, $file, $file, $file...*/)
  {
    $args = func_get_args();
    foreach($args as $file)
    {
      $confAsIni = $this->getString($file);
      $config = parse_ini_string($confAsIni, true);
      $this->mergeConfig($config);
    }
  }

  public function search($term, $field = null)
  {
    $res = $this->db->all($sql = "SELECT * FROM {$this->table} WHERE value LIKE :term", array(':term' => "%{$term}%"));
    foreach($res as $r)
    {
      $cfg = parse_ini_string($r['value'], true);
      $cfg['__id__'] = $r['id'];
      if($field !== null)
      {
        if(is_array($field))
        {
          list($k, $v) = each($field);
          if(isset($cfg[$k][$v]) && $cfg[$k][$v] == $term)
            return $cfg;
        }
        else
        {
          if(isset($cfg[$field]))
            return $cfg;
        }
      }
    }
    return false;
  }

  public function write($file, $string, $alias_of = null)
  {
    $isAlias = $this->isAlias($file);
    $file = $this->getFilePath($file);
    if($isAlias !== null) // isAlias returns null if the record does not exist
    {
      $params = array(':value' => $string);
      $sql = "UPDATE {$this->table} SET value=:value ";
      if($alias_of !== null)
      {
        $sql .= ", alias_of=:alias_of ";
        $params[':alias_of'] = $this->getFilePath($alias_of);
      }
      $params[':file'] = $file;
      if(!$isAlias)
        $sql .= " WHERE id=:file";
      else
        $sql .= " WHERE alias_of=:file";
      $res = $this->db->execute($sql, $params);
    }
    else
    {
      $res = $this->db->execute("INSERT INTO {$this->table} (id, value, alias_of) VALUES(:file, :value, :alias_of)", array(':file' => $file, ':value' => $string, ':alias_of' => $alias_of));
    }

    // delete the cached entry
    if($this->cacheObj)
      $this->cache($file, null);

    return $res !== false;
  }

  private function getFilePath($file)
  {
    return basename($file);
  }

  public function getRecord($file)
  {
    if($file == '')
    {
      EpiException::raise(new EpiConfigException("Configuration file cannot be empty when calling getRecord"));
      return; // need to simulate same behavior if exceptions are turned off
    }

    $file = $this->getFilePath($file);
    if($this->cacheObj)
    {
      $value = $this->cache($file);
      if($value !== null)
        return $value;
    }

    $res = $this->db->one("SELECT * FROM {$this->table} WHERE id=:file OR alias_of=:alias_of", array(':file' => $file, ':alias_of' => $file));
    if(!$res)
    {
      EpiException::raise(new EpiConfigException("Config file ({$file}) does not exist in db"));
      return; // need to simulate same behavior if exceptions are turned off
    }

    if($this->cacheObj)
      $this->cache($file, $res);

    return $res;
  }
}
