<?php
namespace Stanford\CustomSurveyLandingPage;
/** @var \Stanford\CustomSurveyLandingPage\CustomSurveyLandingPage $module */

use HtmlPage;

$HtmlPage = new HtmlPage();
$HtmlPage->PrintHeaderExt();


$title = $module->getProjectSetting('title');
$desc = $module->getProjectSetting('desc');
$input_label = $module->getProjectSetting('input-label');
$input_placeholder = $module->getProjectSetting('placeholder');

// Show the results

?>
<div class="center-container">
    <div class="item card card.bg-primary.text-white">
        <?php if (!empty($title)) { ?>
            <div class="card-header">
                <?php echo $title ?>
            </div>
        <?php }
        if (!empty($desc)) { ?>
        <div class="card-body">
            <?php echo $desc; ?>
        </div>
        <?php } ?>
        <div class="card-footer">
            <form method="POST" action="<?php echo APP_PATH_SURVEY_FULL ?>">

                <div class="input-group input-group-sm mb-3">
                    <?php if (!empty($input_label)) { ?>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="id_label"><?php echo  $input_label ?></span>
                        </div>
                    <?php } ?>
                        <input type="text" class="form-control"  placeholder="<?php echo $input_placeholder ?>" name="code" value="" aria-describedby="id_label">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Go</button>
                        </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $('input[name="code"]').focus();
</script>
<style>
    body {
        background: url(<?php echo $module->getImage64()?>) no-repeat center center fixed;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
        <?php
            global $project_id;
            if ($module->getProjectSetting('align-top', $project_id )) {
                echo "background-position: center top;\n";
            }
        ?>
    }

    #pagecontainer{
        background:none;
    }
    .panel-body {
        background: #fefefe;
    }
    .center-container
    {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
    }
    .item
    {
        vertical-align:middle;
        border-radius: 3px;
        max-width: 300px;
    }
</style>