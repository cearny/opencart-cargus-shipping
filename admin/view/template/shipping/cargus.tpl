<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/shipping.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td colspan=2>
                <span class="help">Configurare modul transport prin Cargus.</span>
            </td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_status; ?></td>
            <td><select name="cargus_status">
                <?php if ($cargus_status) { ?>
                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                <option value="0"><?php echo $text_no; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_yes; ?></option>
                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td colspan=2>
                <span class="help"><b>Autentificare:</b></span>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_cod_client; ?></td>
            <td><input type="text" name="cargus_cod_client" value="<?php echo $cargus_cod_client; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_pin; ?></td>
            <td><input type="text" name="cargus_pin" value="<?php echo $cargus_pin; ?>" /></td>
          </tr>
          <tr>
            <td colspan=2>
                <span class="help"><b>Optiuni expeditie:</b></span>
            </td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_serviciu_id; ?></td>
            <td><input type="text" name="cargus_serviciu_id" value="<?php echo $cargus_serviciu_id; ?>" />
              <?php if ($error_serviciu_id) { ?>
              <span class="error"><?php echo $error_serviciu_id; ?></span>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_tip_colet_id; ?></td>
            <td><input type="text" name="cargus_tip_colet_id" value="<?php echo $cargus_tip_colet_id; ?>" />
              <?php if ($error_tip_colet_id) { ?>
              <span class="error"><?php echo $error_tip_colet_id; ?></span>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td><span class="required">*</span> <?php echo $entry_localitate_origine_id; ?></td>
            <td><input type="text" name="cargus_localitate_origine_id" value="<?php echo $cargus_localitate_origine_id; ?>" />
              <?php if ($error_localitate_origine_id) { ?>
              <span class="error"><?php echo $error_localitate_origine_id; ?></span>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_retur_nt_semnata; ?></td>
            <td><select name="cargus_retur_nt_semnata">
                <?php if ($cargus_retur_nt_semnata) { ?>
                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                <option value="0"><?php echo $text_no; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_yes; ?></option>
                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_retur_alte_documente; ?></td>
            <td><select name="cargus_retur_alte_documente">
                <?php if ($cargus_retur_alte_documente) { ?>
                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                <option value="0"><?php echo $text_no; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_yes; ?></option>
                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td colspan=2>
                <span class="help"><b>Optiuni comportment modul:</b></span>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_min_gratuit; ?></td>
            <td><input type="text" name="cargus_min_gratuit" value="<?php echo $cargus_min_gratuit; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_max_acceptabil_val; ?></td>
            <td><input type="text" name="cargus_max_acceptabil_val" value="<?php echo $cargus_max_acceptabil_val; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_max_acceptabil_wgt; ?></td>
            <td><input type="text" name="cargus_max_acceptabil_wgt" value="<?php echo $cargus_max_acceptabil_wgt; ?>" /></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>