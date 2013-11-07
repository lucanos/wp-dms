<?php $dms_use = get_option( 'dms_use' , array() ); ?>
<div id="screen-meta" class="metabox-prefs"<?php echo ( !count( $dms_use ) ? ' style="display:block"' : '' ); ?>>
  <div id="screen-options-wrap" class="hidden"<?php echo ( !count( $dms_use ) ? ' style="display:block"' : '' ); ?>>
    <form id="adv-settings" action="options.php" method="post">
      <h5>Available Post Types</h5>
      <em>Select which Builtin- and Custom Post Types should be available for DMS.</em>
      <div class="metabox-prefs">
        <label>
          <input class="hide-postbox-tog" name="dms_use[]" type="checkbox" value="page"<?php echo ( in_array( 'page' , $dms_use ) ? ' checked="checked"' : '' ); ?> />
          Pages
        </label>
        <label>
          <input class="hide-postbox-tog" name="dms_use[]" type="checkbox" value="post"<?php echo ( in_array( 'post' , $dms_use ) ? ' checked="checked"' : '' ); ?> />
          Posts
        </label>
        <label>
          <input class="hide-postbox-tog" name="dms_use[]" type="checkbox" value="categories"<?php echo ( in_array( 'categories' , $dms_use ) ? ' checked="checked"' : '' ); ?> />
          Blog Categories
        </label>
<?php

$types = DMS::getCustomPostTypes();
if( count( $types ) ){
?>
        <br class="clear">
  <?php
  foreach( $types as $type ){
    $name  = $type['name'];
    $label = $type['label'];
?>
        <label for="<?php echo $name; ?>">
          <input class="hide-postbox-tog" name="dms_use[]" type="checkbox" value="<?php echo $name; ?>"<?php echo ( in_array( $name , $dms_use ) ? ' checked="checked"' : '' ); ?> />
          <?php echo $label; ?>
        </label>

<?php
    if( $type['has_archive'] ){
      $name  .= '_archive';
      $label .= ' <strong>Archive</strong>';
?>
        <label for="<?php echo $name; ?>">
          <input class="hide-postbox-tog" name="dms_use[]" type="checkbox" value="<?php echo $name; ?>"<?php echo ( in_array( $name , $dms_use ) ? ' checked="checked"' : '' ); ?> />
          <?php echo $label; ?>
        </label>

<?php
    }
  }
}
?>
        <br class="clear">
        <?php settings_fields( 'dms_config' ); ?>
        <p class="submit">
          <input type="submit" class="button-primary" value="Save" />
        </p>
      </div>
    </form>
  </div>
</div>
<div id="screen-meta-links">
  <div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle">
    <a href="#screen-options-wrap" id="show-settings-link" class="show-settings screen-meta-active">Configure DMS</a>
  </div>
</div>
<!-- Actual Stuff -->
<div class="wrap">
  <?php echo screen_icon(); ?>
  <h2>Domain Mapping System Configuration</h2>
<?php
if( !count( $dms_use ) ){
?>
  <div class="error">
    <p><strong>Warning!</strong></p>
    <p>You cannot edit your DMS Settings until you select one or more Post Types to map.</p>
  </div>
<?php
}else{
?>
  <div class="updated" style="background:#BDE5F8;border-color:#00529B">
    <p><strong>Warning!</strong></p>
    <p>Only change these settings if you're <em>absolutely</em> sure what you're doing. Changing these settings might break the internet. Seriously.</p>
  </div>
  <form method="post" action="options.php">
    <fieldset class="dms">
      <legend>
        DMS Map
      </legend>
      <table class="form-table" id="dms-map">
        <tr valign="top">
          <th scope="row">
            <label>Domains</label>
          </th>
        </tr>
<?php

  $options = DMS::getDMSOptions();
  $map = array_combine( get_option( 'map_domain' , array() ) , get_option( 'map_target' , array() ) );

  foreach( $map as $key => $value ){
?>
        <tr>
          <th></th>
          <td>
            <span class="pre-host">http(s)://</span>
            <input name="map_domain[]" type="text" class="dms regular-text dms-collect-key" value="<?php echo str_replace( '_' , '.' , $key ); ?>" placeholder="www.example.com"/>
            <span class="post-host">/</span>
            <select name="map_target[]" class="dms page_id" data-placeholder="The choice is yours.">
              <option></option>
<?php
    foreach( $options as $key => $optgroup ){
?>
              <optgroup label="<?php echo $key; ?>">
<?php
      foreach( $optgroup as $o ){
?>
                <option<?php echo ( $o['id']===$value ? ' selected="selected"' : '' ); ?> class="level-0" value="<?php echo $o['id']; ?>"><?php echo $o['title']; ?></option>
<?php
      }
?>
              </optgroup>
<?php
    }
?>
            </select>
            <button class="dms-delete-row" title="Delete">&times;</button>
          </td>
        </tr>
<?php
  }
?>
        <tr>
          <th></th>
          <td>
            <span class="pre-host">http(s)://</span>
            <input name="map_domain[]" type="text" class="dms regular-text dms-collect-key" placeholder="www.example.com" />
            <span class="post-host">/</span>
            <select name="map_target[]" class="dms page_id" data-placeholder="The choice is yours.">
              <option></option>
<?php
  foreach( $options as $key => $optgroup ){
?>
              <optgroup label="<?php echo $key; ?>">
<?php
    foreach( $optgroup as $o ){
?>
                <option class="level-0" value="<?php echo $o['id']; ?>"><?php echo $o['title']; ?></option>
<?php
    }
?>
              </optgroup>
<?php
  }
?>
            </select>
            <button class="dms-delete-row" title="Delete">&times;</button>
          </td>
        </tr>
        <tr id="dms-add-new-tr">
          <th></th>
          <td>
            <strong><a class="dms-add-row" href="#">+ Add Domain Map Entry</a></strong>
          </td>
        </tr>
      </table>
    </fieldset>
    <?php settings_fields( 'dms_storage' ); ?>
    <p class="submit">
      <input type="submit" class="button-primary" value="Save" id="dms-submit-config" />
    </p>
  </form>
<?php
}
?>
</div>
