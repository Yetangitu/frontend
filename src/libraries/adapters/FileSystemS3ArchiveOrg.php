<?php
/**
 * Archive.org adapter that extends much of the FileSystemS3 logic
 *
 * This class defines the functionality defined by FileSystemInterface for a plain Filesystem.
 * @author Jaisen Mathai <jaisen@jmathai.com>
 */
class FileSystemS3ArchiveOrg extends FileSystemS3 implements FileSystemInterface
{
  private $root;
  private $urlBase;
  private $archiveOrg;

  public function __construct()
  {
    parent::__construct();
    $this->archiveOrg = new FileSystemArchiveOrgBase($this);
  }

  public function deletePhoto($photo)
  {
    // delete original here and rest in s3
    $myPhoto = array('path_original' => $photo['path_original']);
    $parentPhoto = $myPhoto;
    unset($parentPhoto['path_original']);
    return $this->archiveOrg->deletePhoto($myPhoto) && parent::deletePhoto($parentPhoto);
  }
  /**
    * Gets diagnostic information for debugging.
    *
    * @return array
    */
  public function diagnostics()
  {
    return array_merge($this->archiveOrg->fs->diagnostics(), parent::diagnostics());
  }

  public function downloadPhoto($photo)
  {
    $path_original = str_replace($this->getHost(), $this->archiveOrg->getHost(), $photo['path_original']);
    $fp = fopen($path_original, 'r');
    return $fp;
  }

  /**
    * Executes an upgrade script
    *
    * @return void
    */
  public function executeScript($file, $filesystem)
  {
    if($filesystem == 'archiveOrg')
      echo file_get_contents($file);
    else
      parent::executeScript($file, $filesystem);
  }

  /**
   * Get photo will copy the photo to a temporary file.
   *
   */
  public function getPhoto($filename)
  {
    if(strpos($filename, '/original/') === false)
      return parent::getPhoto($filename);
    else
      return $this->archiveOrg->fs->getPhoto($filename);
  }

  public function putPhoto($localFile, $remoteFile, $date_taken)
  {
    if(strpos($remoteFile, '/original/') === false)
      return parent::putPhoto($localFile, $remoteFile, $date_taken);
    else
      return $this->archiveOrg->fs->putPhoto($localFile, $remoteFile, $date_taken);
  }

  public function putPhotos($files)
  {
    // this creates two local variables
    extract($this->archiveOrg->segmentFiles($files));

    return $this->archiveOrg->fs->putPhotos($myFiles) && parent::putPhotos($parentFiles);
  }

  public function initialize($isEditMode)
  {
    return $this->archiveOrg->fs->initialize($isEditMode) && parent::initialize($isEditMode);
  }

  /**
    * Identification method to return array of strings.
    *
    * @return array
    */
  public function identity()
  {
    return array_merge(array('archiveOrg'), parent::identity());
  }

  public function normalizePath($path)
  {
    return parent::normalizePath($path);
  }
}
