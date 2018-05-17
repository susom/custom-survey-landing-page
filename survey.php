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
    <div class="item panel panel-primary">
        <?php if (!empty($title)) { ?>
            <div class="panel-heading">
                <?php echo $title ?>
            </div>
        <?php }
        if (!empty($desc)) { ?>
        <div class="panel-body">
            <?php echo $desc; ?>
        </div>
        <?php } ?>
        <div class="panel-footer">
            <form method="POST" action="<?php echo APP_PATH_SURVEY_FULL ?>">

                <div class="input-group">
                    <?php if (!empty($input_label)) { ?>
                        <span class="input-group-addon" id="id_label"><?php echo $input_label ?></span>
                    <?php } ?>
                    <input type="text" class="form-control" placeholder="<?php echo $input_placeholder ?>" name="code" value="" aria-describedby="id_label">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary">Go</button>
                    </span>
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