<div id="setup">
  <h1>Create your Trovebox site <?php if(empty($qs)) { ?><em><a href="/setup/restart">(start over)</a></em><?php } ?></h1>
  <ul class="nav nav-pills">
    <?php for($i = 1; $i <= 3; $i++) { ?>
      <li<?php echo ($step == $i) ? ' class="active"' : ''; ?>><a><?php echo $i; ?></a></li>
    <?php } ?>
  </ul>
  <?php if(!empty($errors)) { ?>
    <?php if(is_array($errors)) { ?>
      <div class="alert alert-error">
        <strong>Please fix the following errors</strong>
        <ul class="errors">
          <?php foreach($errors as $error) { ?>
            <li><?php echo $error; ?></li>
          <?php } ?>
        </ul>
      </div>
    <?php } else { ?>
      <p class="alert alert-error error">
        <strong>Please fix the following errors</strong>
        <?php echo $errors; ?>
      </p>
    <?php } ?>
  <?php } ?>
  <div id="setup-step-1"<?php echo ($step != 1) ? ' class="hidden"' : ''?>>
    <form class="validate" action="/setup<?php echo $qs ?>" method="post">
      <h2><?php if(isset($_GET['edit'])) { ?>Create Account<?php } else { ?>Account Settings<?php } ?></h2>
      <label for="email">Email address</label>
      <input type="text" name="email" id="email" placeholder="user@example.com" <?php if(isset($email)) { ?>value="<?php $this->utility->safe($email); ?>"<?php } ?> data-validation="required email">

      <?php if($this->config->site->allowOpenPhotoLogin == 1) { ?>
        <label for="email">Password</label>
        <input type="password" name="password" id="password" placeholder="password" <?php if(isset($password)) { ?>value="<?php $this->utility->safe($password); ?>"<?php } ?> data-validation="required">
      <?php } else { ?>
        <input type="hidden" name="password" value="">
      <?php } ?>

      <input type="hidden" name="theme" value="fabrizio1.0">

      <div class="btn-toolbar">
        <?php if(isset($_GET['edit'])) { ?><a class="btn" href="/">Cancel</a><?php } ?>
        <button class="btn btn-brand" type="submit">Continue to Step 2</button>
      </div>
      <input type="hidden" name="app_id" id="app_id" <?php if(isset($app_id)) { ?>value="<?php $this->utility->safe($app_id); ?>"<?php } ?>>
    </form>
  </div>
  <div id="setup-step-2"<?php echo ($step != 2) ? ' class="hidden"' : ''?>>
    <form action="/setup/2<?php echo $qs; ?>" method="post">
      <h2>Site Settings <em>(the defaults work just fine<!--<a href="">what's this?</a>-->)</em></h2>
      <label for="imageLibrary">Select Image Library (see <a href="https://github.com/photo/frontend/issues/662" target="_blank">#662</a> if using GD)</label>
      <?php if(isset($imageLibs)) { ?>
        <select name="imageLibrary" id="imageLibrary">
          <?php foreach($imageLibs as $key => $val) { ?>
            <option value="<?php echo $key; ?>"<?php echo ($imageLibrary == $key) ? ' selected="selected"' : '' ?>><?php echo $val; ?></option>
          <?php } ?>
        </select>
      <?php } ?>

      <label for="database">Select database</label>
      <?php if(isset($databases)) { ?>
        <select name="database" id="database">
          <?php foreach($databases as $key => $val) { ?>
            <option value="<?php echo $key; ?>"<?php echo ($database == $key) ? ' selected="selected"' : '' ?>><?php echo $val; ?></option>
          <?php } ?>
        </select>
      <?php } ?>

      <label for="fileSystem">Select File System</label>
      <select name="fileSystem">
        <option value="S3"<?php echo ($filesystem == 'S3') ? ' selected="selected"' : '' ?>>Amazon S3</option>
        <option value="S3Dropbox"<?php echo ($filesystem == 'S3Dropbox') ? ' selected="selected"' : '' ?>>Amazon S3 + Dropbox</option>
        <option value="Local"<?php echo ($filesystem == 'Local') ? ' selected="selected"' : '' ?>>Local filesystem</option>
        <option value="LocalDropbox"<?php echo ($filesystem == 'LocalDropbox') ? ' selected="selected"' : '' ?>>Local filesystem + Dropbox</option>
        <!--<option value="DreamObjects"<?php echo ($filesystem == 'DreamObjects') ? ' selected="selected"' : '' ?>>DreamObjects</option>-->
      </select>

      <div class="btn-toolbar">
        <?php if(isset($_GET['edit'])) { ?><a class="btn" href="/">Cancel</a><?php } ?>
        <button type="submit" class="btn btn-brand">Continue to Step 3</button>
      </div>
    </form>
  </div>
  <div id="setup-step-3"<?php echo ($step != 3) ? ' class="hidden"' : ''?>>
    <form class="validate" action="/setup/3<?php echo $qs; ?>" method="post">
      <h2>Credentials<!-- <em>(<a href="">what's this?</a>)</em>--></h2>
      <?php if(isset($usesAws) && $usesAws) { ?>
        <h3>Enter your Amazon credentials <em>(<a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key" target="_blank">what's this?</a>)</em></h3>
        <label for="awsKey">Amazon Access Key ID</label>
        <input type="password" name="awsKey" id="awsKey" placeholder="Your AWS access key" size="50" autocomplete="off" data-validation="required" value="<?php echo $awsKey; ?>">

        <label for="awsSecret">Amazon Secret Access Key</label>
        <input type="password" name="awsSecret" id="awsSecret" placeholder="Your AWS access secret" size="50" autocomplete="off" data-validation="required" value="<?php echo $awsSecret; ?>">

        <label for="s3Bucket">Amazon S3 Bucket Name</label>
        <?php if(isset($usesS3) && $usesS3) { ?>
          <?php if(isset($s3Bucket) && !empty($s3Bucket)) { ?>
            <input type="text" name="s3Bucket" id="s3Bucket" size="50" placeholder="Globally unique bucket name" value="<?php $this->utility->safe($s3Bucket); ?>" data-validation="required">
          <?php } else { ?>
            <input type="text" name="s3Bucket" id="s3Bucket" size="50" placeholder="Globally unique bucket name" value="<?php $this->utility->safe($_SERVER['HTTP_HOST']); ?>" data-validation="required">
          <?php } ?>
        <?php } ?>
      <?php } ?>

      <?php if(isset($usesMySql) && $usesMySql) { ?>

      <h3>Enter your MySql credentials <!--<em>(<a href="">what's this?</a>)</em>--></h3>

      <label for="mySqlHost">MySQL Host <em>(port is optional)</em></label>
      <input type="text" name="mySqlHost" id="mySqlHost" placeholder="Your MySql host (i.e. 127.0.0.1:3306)" size="50" autocomplete="off" data-validation="required" value="<?php echo $mySqlHost; ?>">

      <label for="mySqlUser">MySQL Username</label>
      <input type="text" name="mySqlUser" id="mySqlUser" placeholder="Your MySql username" size="50" autocomplete="off" data-validation="required" value="<?php echo $mySqlUser; ?>">

      <label for="mySqlPassword">MySQL Password</label>
      <input type="password" name="mySqlPassword" id="mySqlPassword" placeholder="Your MySql password" size="50" autocomplete="off" value="<?php echo $mySqlPassword; ?>">

      <label for="mySqlDb">MySQL Database <em>(make sure this database already exists)</em></label>
      <input type="text" name="mySqlDb" placeholder="Name of your MySql database" id="mySqlDb" size="50" autocomplete="off" data-validation="required" value="<?php echo $mySqlDb; ?>">

      <label for="mySqlTablePrefix">Table prefix <em>(optional)</em></label>
      <input type="text" name="mySqlTablePrefix" placeholder="A prefix for all OpenPhoto tables" id="mySqlTablePrefix" size="50" autocomplete="off" value="<?php echo $mySqlTablePrefix; ?>">

      <?php } ?>

      <?php if(isset($usesPostgreSql) && $usesPostgreSql) { ?>

      <h3>Enter your PostgreSQL credentials <!--<em>(<a href="">what's this?</a>)</em>--></h3>

      <label for="postgreSqlHost">PostgreSQL Host <em>(port is optional)</em></label>
      <input type="text" name="postgreSqlHost" id="postgreSqlHost" placeholder="Your PostgreSql host (i.e. 127.0.0.1:3306)" size="50" autocomplete="off" data-validation="required" value="<?php echo $postgreSqlHost; ?>">

      <label for="postgreSqlUser">PostgreSQL Username</label>
      <input type="text" name="postgreSqlUser" id="postgreSqlUser" placeholder="Your PostgreSql username" size="50" autocomplete="off" data-validation="required" value="<?php echo $postgreSqlUser; ?>">

      <label for="postgreSqlPassword">PostgreSQL Password</label>
      <input type="password" name="postgreSqlPassword" id="postgreSqlPassword" placeholder="Your PostgreSql password" size="50" autocomplete="off" value="<?php echo $postgreSqlPassword; ?>">

      <label for="postgreSqlDb">PostgreSQL Database <em>(make sure this database already exists)</em></label>
      <input type="text" name="postgreSqlDb" placeholder="Name of your PostgreSql database" id="postgreSqlDb" size="50" autocomplete="off" data-validation="required" value="<?php echo $postgreSqlDb; ?>">

      <label for="postgreSqlTablePrefix">Table prefix <em>(optional)</em></label>
      <input type="text" name="postgreSqlTablePrefix" placeholder="A prefix for all OpenPhoto tables" id="postgreSqlTablePrefix" size="50" autocomplete="off" value="<?php echo $postgreSqlTablePrefix; ?>">

      <?php } ?>

      <?php if((isset($usesLocalFs) && !empty($usesLocalFs))) { ?>
        <h3>Enter your local file system credentials <!--<em>(<a href="">what's this?</a>)</em>--></h3>
        <label for="fsRoot">File system root <em>(Must be writable by web server user)</em></label>
        <input type="text" name="fsRoot" id="fsRoot" size="50" placeholder="/home/username/openphoto/src/html/photos (full path to writable directory)" data-validation="required" value="<?php echo $fsRoot; ?>">

        <label for="fsHost">File system hostname for download URL <em>(Web accessible w/o "http://")</em></label>
        <input type="text" name="fsHost" id="fsHost" size="50" placeholder="example.com/photos (no http:// or trailing slash)" data-validation="required" value="<?php echo $fsHost; ?>">
      <?php } ?>
      <?php if(isset($usesDropbox) && !empty($usesDropbox)) { ?>
        <input type="hidden" name="dropboxKey" value="<?php $this->utility->safe($dropboxKey); ?>">
        <input type="hidden" name="dropboxSecret" value="<?php $this->utility->safe($dropboxSecret); ?>">
        <input type="hidden" name="dropboxToken" value="<?php $this->utility->safe($dropboxToken); ?>">
        <input type="hidden" name="dropboxTokenSecret" value="<?php $this->utility->safe($dropboxTokenSecret); ?>">
        <input type="hidden" name="dropboxFolder" value="<?php $this->utility->safe($dropboxFolder); ?>">
      <?php } ?>

      <div class="btn-toolbar">
        <?php if(isset($_GET['edit'])) { ?><a class="btn" href="/">Cancel</a><?php } ?>
        <button type="submit" class="btn btn-brand">Complete setup</button>
      </div>
    </form>
  </div>
</div>
