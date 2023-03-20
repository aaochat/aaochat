<?php
/**
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use OCP\IURLGenerator;
use OCP\IConfig;
use OCA\aaochat\AppInfo\Application;
use OCA\AaoChat\Service\AaochatService;

script('aaochat', 'admin');
style('aaochat', 'admin');

$urlGenerator = \OC::$server[IURLGenerator::class];

$extraFieldClass = 'hide';
$licensekeyExists = 'no';
if(isset($_['aaochat_license_key']) && !empty($_['aaochat_license_key'])) {
    $extraFieldClass = 'show';
    $licensekeyExists = 'yes';
}

$isLeadEditAllowed = 'yes';
$isLeadCreated = 'no';
if(isset($_['aaochat_lead_id']) && !empty($_['aaochat_lead_id'])) {
    $isLeadCreated = 'yes';
    $isLeadEditAllowed = 'no';
}

$isLicenseValid = 'no';
if(isset($_['aaochat_license_status']) && $_['aaochat_license_status'] == 'active') {
    $isLicenseValid = 'yes';
    $isLeadEditAllowed = 'yes';
}

?>

<div id="side-menu-section">

    <div class="section">
         <h2>
            <?php p($l->t('Aao Business Chat Settings')); ?>
        </h2>
    </div>

<div id="aaochat-lead">
        <div class="section">
            <h3>
                <b><?php p($l->t('Register')); ?></b>
            </h3>

            <div class="side-menu-setting-table " >
                <input type="hidden" class="side-menu-setting" id="aaochat_lead_id" name="aaochat_lead_id" value="<?php echo $_['aaochat_lead_id']; ?>" style="width: 100%;" <?php echo ($isLeadEditAllowed=='no')? 'readonly="readonly"': '';?>>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Full Name')); ?>
                        <span style="color:red;">*</span>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" placeholder="<?php p($l->t('Full Name')); ?>" class="side-menu-setting" id="aaochat_lead_name" name="aaochat_lead_name" value="<?php echo $_['aaochat_lead_name']; ?>" style="width: 100%;" <?php echo ($isLeadEditAllowed=='no')? 'readonly="readonly"': '';?>>
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Email')); ?>
                        <span style="color:red;">*</span>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" placeholder="<?php p($l->t('Email')); ?>" class="side-menu-setting" id="aaochat_lead_email" name="aaochat_lead_email" value="<?php echo $_['aaochat_lead_email']; ?>" style="width: 100%;" <?php echo ($isLeadEditAllowed=='no')? 'readonly="readonly"': '';?>>
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Phone Number')); ?>
                        <span style="color:red;">*</span>
                    </div>
                    <div class="side-menu-setting-form">
                        <select id="aaochat_lead_phone_contry_code" name="aaochat_lead_phone_contry_code" style="width: 25%; float:left; padding: 5px; margin: 3px 0px;" <?php echo ($isLeadEditAllowed=='no')? 'disabled="disabled"': '';?>>
                        <?php
                            $phoneCountryCode = AaochatService::phoneCountryCode();
                            foreach($phoneCountryCode as $countryCode => $phoneCountry) {
                            ?>
                            <option data-countryCode="<?php echo $countryCode;?>" value="<?php echo $phoneCountry['code'];?>" <?php if($_['aaochat_lead_phone_contry_code']== $phoneCountry['code']) {?> selected="selected" <?php }?>><?php echo $phoneCountry['code'];?></option>
                            <?php
                            }
                        ?>
                        </select>
                        <input type="text" placeholder="<?php p($l->t('Phone Number')); ?>" class="side-menu-setting aaochat_lead_phone" id="aaochat_lead_phone" name="aaochat_lead_phone" value="<?php echo $_['aaochat_lead_phone']; ?>" style="width: 73%; float:left;" <?php echo ($isLeadEditAllowed=='no')? 'readonly="readonly"': '';?>>
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Country')); ?>
                        <span style="color:red;">*</span>
                    </div>
                    <div class="side-menu-setting-form">
                        <select id="aaochat_lead_country" name="aaochat_lead_country" style="width: 100%; padding: 5px; margin: 3px 0px;" <?php echo ($isLeadEditAllowed=='no')? 'disabled="disabled"': '';?>>
                        <?php
                            $countries = AaochatService::getCountries();
                            foreach($countries as $countryIndex => $countryName) {
                            ?>
                            <option value="<?php echo $countryIndex;?>" <?php if($_['aaochat_lead_country']== $countryIndex) {?> selected="selected" <?php }?>><?php echo $countryName;?></option>
                            <?php
                            }
                        ?>
                        </select>
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Organization')); ?>
                        <span style="color:red;">*</span>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" placeholder="<?php p($l->t('Organization')); ?>" class="side-menu-setting" id="aaochat_lead_organization" name="aaochat_lead_organization" value="<?php echo $_['aaochat_lead_organization']; ?>" style="width: 100%;" <?php echo ($isLeadEditAllowed=='no')? 'readonly="readonly"': '';?>>
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Organization Address')); ?>
                        <span style="color:red;">*</span>
                    </div>
                    <div class="side-menu-setting-form">
                        <textarea cols="100" placeholder="<?php p($l->t('Organization Address')); ?>" rows="3" class="side-menu-setting" id="aaochat_lead_organization_address" name="aaochat_lead_organization_address" <?php echo ($isLeadEditAllowed=='no')? 'readonly="readonly"': '';?> style="width: 100%;"><?php echo $_['aaochat_lead_organization_address']; ?></textarea>
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Organization Site URL')); ?>
                        <span style="color:red;">*</span>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" placeholder="<?php p($l->t('Organization Site URL')); ?>" class="side-menu-setting" id="aaochat_lead_organization_siteurl" name="aaochat_lead_organization_siteurl" value="<?php echo $_['aaochat_lead_organization_siteurl']; ?>" style="width: 100%;" <?php echo ($isLeadEditAllowed=='no')? 'readonly="readonly"': '';?>>
                    </div>
                </div>
                <?php if($isLeadCreated=='yes') { ?>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Status')); ?>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" class="side-menu-setting" id="aaochat_lead_status" name="aaochat_lead_status" value="<?php echo $_['aaochat_lead_status']; ?>" style="width: 100%;" <?php echo ($isLeadEditAllowed=='no')? 'readonly="readonly"': '';?>>
                    </div>
                </div>
                <?php } ?>
                <div class="side-menu-setting-row aaochat_lead_status_msg_container">
                    <div id="aaochat_lead_status_msg" ></div>
                </div>
            </div>
        </div>


        <div class="section" id="more">

            <?php if($licensekeyExists=='yes') { ?>
                <button id="side-menu-lead-create" name="update" class="btn btn-info" <?php echo ($isLeadCreated=='yes')? 'disabled="disabled"': '';?>>
                    <?php p($l->t('Update')); ?>
                </button>
            <?php } else { ?>
            <button id="side-menu-lead-create" name="register" class="btn btn-info" <?php echo ($isLeadCreated=='yes')? 'disabled="disabled"': '';?>>
                <?php p($l->t('Register')); ?>
            </button>
            <?php } ?>
            <button id="side-menu-lead-status" class="btn btn-info" <?php echo ($isLeadCreated=='no')? 'disabled="disabled"': '';?>>
                <?php p($l->t('Check Status')); ?>
            </button>

            <span id="side-menu-message" class="msg"></span>

            <div style="height: 30px"></div>

        </div>
    </div>

    <div id="activate-license" <?php //echo ($isLeadCreated=='no')? 'style="display:none"': '';?>>
        <div class="section">    

            
            <h3>
                <b><?php p($l->t('Activate License')); ?></b>
            </h3>

            <div class="side-menu-setting-table">
                
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('License Key')); ?>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" class="side-menu-setting" id="aaochat_license_key" name="aaochat_license_key" value="<?php echo $_['aaochat_license_key']; ?>" style="width: 100%;">
                        
                    </div>
                </div>
            </div>
            <div class="side-menu-setting-table aaochat-setting-extra-fields <?php echo $extraFieldClass; ?>" >
                <input type="hidden" class="side-menu-setting" id="aaochat_license_id" name="aaochat_license_id" value="<?php echo $_['aaochat_license_id']; ?>" style="width: 100%;" readonly="readonly">
                <input type="hidden" class="side-menu-setting" id="aaochat_client_id" name="aaochat_client_id" value="<?php echo $_['aaochat_client_id']; ?>" style="width: 100%;" readonly="readonly">
                <input type="hidden" class="side-menu-setting" id="aaochat_ser_storage_size" name="aaochat_ser_storage_size" value="<?php echo $_['aaochat_ser_storage_size']; ?>" style="width: 100%;" readonly="readonly">
                <input type="hidden" class="side-menu-setting" id="aaochat_ser_id" name="aaochat_ser_id" value="<?php echo $_['aaochat_ser_id']; ?>" style="width: 100%;" readonly="readonly">
                <input type="hidden" class="side-menu-setting" id="aaochat_ipaddress" name="aaochat_ipaddress" value="<?php echo $_['aaochat_ipaddress']; ?>" style="width: 100%;" readonly="readonly">
                <?php if($isLicenseValid=='yes') { ?>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('License Status')); ?>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" class="side-menu-setting" id="aaochat_license_status" name="aaochat_license_status" value="<?php echo $_['aaochat_license_status']; ?>" style="width: 100%;" readonly="readonly">
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Is License Valid')); ?>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" class="side-menu-setting" id="aaochat_is_license_valid" name="aaochat_is_license_valid" value="<?php echo $_['aaochat_is_license_valid']; ?>" style="width: 100%;" readonly="readonly">
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Server Instance Type')); ?>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" class="side-menu-setting" id="aaochat_ser_instance_type" name="aaochat_ser_instance_type" value="<?php echo $_['aaochat_ser_instance_type']; ?>" style="width: 100%;" readonly="readonly">
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Server URL')); ?>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" class="side-menu-setting" id="aaochat_ser_url" name="aaochat_ser_url" value="<?php echo $_['aaochat_ser_url']; ?>" style="width: 100%;" readonly="readonly">
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('File Server URL')); ?>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" class="side-menu-setting" id="aaochat_ser_file_server_url" name="aaochat_ser_file_server_url" value="<?php echo $_['aaochat_ser_file_server_url']; ?>" style="width: 100%;" readonly="readonly">
                    </div>
                </div>
                <div class="side-menu-setting-row">
                    <div class="side-menu-setting-label">
                        <?php p($l->t('Activation Date')); ?>
                    </div>
                    <div class="side-menu-setting-form">
                        <input type="text" class="side-menu-setting" id="aaochat_activation_date" name="aaochat_activation_date" value="<?php echo $_['aaochat_activation_date']; ?>" style="width: 100%;" readonly="readonly">
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>


        <div class="section" id="more">
            <!--
            <button id="side-menu-save" class="btn btn-info">
                <?php //p($l->t('Save')); ?>
            </button>
            -->
            <button id="side-menu-license-activate" class="btn btn-info" <?php echo ($isLicenseValid=='yes')? 'disabled="disabled"': '';?>>
                <?php p($l->t('Activate')); ?>
            </button>
            <button id="side-menu-license-surrender" class="btn btn-info" <?php echo ($isLicenseValid=='no')? 'disabled="disabled"': '';?>>
                <?php p($l->t('Surrender')); ?>
            </button>

            <span id="side-menu-message" class="msg"></span>

            <div style="height: 30px"></div>


        </div>
    </div>
</div>
