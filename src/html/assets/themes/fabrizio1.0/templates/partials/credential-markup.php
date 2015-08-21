<h4>Details for your app: <em><?php $this->utility->safe($name); ?></em></h4>
<div class="row">
  <div class="span12">
    <table class="table left-header">
      <tr>
        <td class="span2">Name</td>
        <td><?php $this->utility->safe($name); ?></td>
      </tr>
      <tr>
        <td>consumerKey =</td>
        <td><?php $this->utility->safe($id); ?></td>
      </tr>
      <tr>
        <td>consumerSecret =</td>
        <td><?php $this->utility->safe($client_secret); ?></td>
      </tr>
      <tr>
        <td>token =</td>
        <td><?php $this->utility->safe($user_token); ?></td>
      </tr>
      <tr>
        <td>tokenSecret =</td>
        <td><?php $this->utility->safe($user_secret); ?></td>
      </tr>
      <tr>
        <td>Type</td>
        <td>
          <?php $this->utility->safe($type); ?>
          <?php if($type !== Credential::typeAccess) { ?>
            <small>(Only access tokens can be used)</small>
          <?php } ?>
        </td>
      </tr>
    </table>
  </div>
</div>
<a href="#" class="batchHide close" title="Close this dialog"><i class="icon-remove batchHide"></i></a>
