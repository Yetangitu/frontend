<?php
/**
  * Action controller for HTML endpoints
  *
  * @author Jaisen Mathai <jaisen@jmathai.com>
  */
class ActionController extends BaseController
{
  /**
    * Call the parent constructor
    *
    * @return void
    */
  public function __construct()
  {
    parent::__construct();
    $this->authentication = getAuthentication();
  }

  /**
    * Create a new action by calling the corresponding API endpoint.
    * TODO define the redirect
    *
    * @param string $target_id The ID of the target on which the action will be applied.
    * @param string $target_type The type of object this action is being added to - typically a photo.
    * @return void
    */
  public function create($target_id, $target_type)
  {
    // does not need to be owner, anyone can comment
    $this->authentication->requireAuthentication(false);
    $this->authentication->requireCrumb($_POST['crumb']);
    $res = $this->api->invoke(sprintf('%s.json', $this->url->actionCreate($target_id, $target_type, false)), EpiRoute::httpPost);
    $result = $res ? '1' : '0';
    // TODO: standardize messaging parameter
    if($target_type == 'photo')
      $this->route->redirect(sprintf('%s?c=commented', $this->url->photoView($target_id, null, false)));
    else
      $this->route->run('/error/500');
  }
}
