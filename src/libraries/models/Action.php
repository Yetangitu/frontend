<?php
/**
 * Action model.
 *
 * This handles adding comments, favorites as well as deleting them.
 * @author Jaisen Mathai <jaisen@jmathai.com>
 */
class Action extends BaseModel
{
  /*
   * Constructor
   */
  public function __construct($params = null)
  {
    parent::__construct();
    if(isset($params['user']))
      $this->user = $params['user'];
    else
      $this->user = new User;
  }

  /**
    * Add an action to a photo/video.
    * Accepts a set of params that must include a type and target_type
    *
    * @param array $params Params describing the action to be added
    * @return mixed Action ID on success, false on failure
    */
  public function create($params)
  {
    if(!isset($params['type']) || !isset($params['target_type']))
      return false;

    $id = $this->user->getNextId('action');
    if($id === false)
    {
      $this->logger->crit("Could not fetch next action ID for {$params['type']}");
      return false;
    }
    $params = array_merge($this->getDefaultAttributes(), $params);
    $params['owner'] = $this->owner;
    $params['actor'] = $this->getActor();
    $params['permalink'] = sprintf('%s#action-%s', $params['target_url'], $id);
    $action = $this->db->putAction($id, $params);
    if(!$action)
    {
      $this->logger->crit("Could not save action ID ({$id}) for {$params['type']}");
      return false;
    }

    return $id;
  }

  /**
    * Delete an action to a photo/video.
    *
    * @param string $id ID of the action to be deleted.
    * @return boolean
    */
  public function delete($id)
  {
    return $this->db->deleteAction($id);
  }

  /**
    * Retrieve a specific action.
    *
    * @param string $id ID of the action to be retrieved.
    * @return boolean
    */
  public function view($id)
  {
    return $this->db->getAction($id);
  }

  /**
    * Defines the default attributes for an action.
    * Used when adding an action.
    *
    * @return array
    */
  private function getDefaultAttributes()
  {
    return array(
      'app_id' => $this->config->application->app_id,
      'email' => '',
      'name' => '',
      'avatar' => '',
      'website' => '',
      'target_url' => '',
      'permalink' => '',
      'value' => '',
      'date_posted' => time(),
      'status' => 1
    );
  }
}
