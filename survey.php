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

$img_partner_1 = $module->getAnyImage64("partner-1");
$img_partner_2 = $module->getAnyImage64("partner-2");
$img_partner_3 = $module->getAnyImage64("partner-3");
$footerText = $module->getProjectSetting('footer-text');

$access_type = $_GET["access"];
$access_code_length = $module->getAccessCodeLength($access_type);
$query_string =  $_SERVER['QUERY_STRING'];
//  Remove last access parameter to omit bloated url
if ( strpos($query_string, '&access') !== false) {
    $query_string = substr( $query_string,0,strpos($query_string, '&access') );
}
// Show the results

?>
<div class="page-wrapper">
        <div class="wrapper wrapper-w580">
            <div class="card card-shadow">
                <div class="card-header text-center">
                    <?php if( $module->getAnyImage64("logo") ): ?>
                        <img id="survey-logo" src="<?= $module->getAnyImage64("logo") ?>">
                    <?php elseif(!empty($title)): ?>
                        <h2 class="h2 text-center"><?= $title ?></h2>
                    <?php endif; ?>
                </div>
                <div class="card-body pt-3 pb-5">
                    <div id="selection-wrap" class="hidden">
                        <div class="row">
                            <div class="col-6">
                                <h4><?=$module->tt("code_selection")?></h4>
                            </div>
                            <div class="col-6 text-right">
                                <i id="btn-close-select-code" style="color:grey; cursor:pointer;" class="mt-1 fa fa-2x fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="list-group mt-3">
                            <a href="?<?php echo $query_string ?>&access=default" class="list-group-item list-group-item-action <?php if($access_type != 'short' && $access_type != 'numeral') echo 'disabled' ?>">
                                <?= $module->tt("code_choice_default")?>
                            </a>
                            <a href="?<?php echo $query_string ?>&access=short" class="list-group-item list-group-item-action <?php if($access_type == 'short') echo 'disabled' ?> ">
                                <?= $module->tt("code_choice_short")?>
                            </a>
                            <a href="?<?php echo $query_string ?>&access=numeral" class="list-group-item list-group-item-action <?php if($access_type == 'numeral') echo 'disabled' ?>">
                                <?=$module->tt("code_choice_numeral")?>
                            </a>
                        </div>
                    </div>
                    <div id="main-wrap" class="form-wrap">
                        <div id="alert-error" class="alert alert-danger hidden" role="alert">
                            <i class="fa fa-error-circle"></i>
                            <?=$module->tt("alert_error")?>
                        </div>                   
                        <div id="alert-info" class="alert alert-light" role="alert">
                            <i class="fa fa-info-circle"></i>
                            <?=$module->tt("alert_info", $access_code_length)?>                            
                        </div>
                        <div id="alert-ready" class="alert alert-success hidden" role="alert">
                            <i class="fa fa-check-circle"></i>
                            <?=$module->tt("alert_ready")?>
                        </div>
                        <div id="alert-success" class="alert alert-success hidden" role="alert">
                            <i class="fa fa-check-circle"></i>
                            <?=$module->tt("alert_success")?>
                        </div>                        
                        <form name="access-code-form">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                    <div id="access-code-digit-group" class="input-group mb-3">
                                        <?php 

                                        for ($i=1; $i <= $access_code_length; $i++) { 
                                            echo '<input id="access-digit-'.$i.'" data-pos="'.$i.'" size="1" maxLength="1" type="text" class="form-control stacked">';
                                        }
                                        ?>
                                        <!-- input that actually is being submitted -->
                                        <input name="code" id="code" type="hidden">                     
                                    </div>
                                </div>
                            </div>
                            <div class="p-t-15 text-center">
                                <button id="submit-btn" class="btn btn-lg btn-dark" type="submit" disabled>
                                    <span class="not-loading"><?=$module->tt("check_access")?></span>
                                    <span class="connecting hidden"><?=$module->tt("connecting")?></span>
                                    <span class="loading spinner-border hidden" role="status" aria-hidden="true"></span>
                                    <span class="loading hidden"><?=$module->tt("checking")?></span>
                                </button>                        
                            </div>
                            <?php if( !$module->getProjectSetting("hide-code-alternative")): ?>
                            <div class="text-center small mt-2">
                                <span id="btn-open-select-code" style="text-decoration: underline; cursor: pointer;"><?=$module->tt("code_alternative") ?></span>
                            </div>
                            <?php endif; ?>
                        </form>
                        <!-- hidden form to send the actual request for redirect -->
                        <form id="redirect_form" method="POST" action="<?php echo APP_PATH_SURVEY_FULL ?>">
                            <input name="code" id="code_redirect" type="hidden">
                        </form>
                    </div>
                </div>
                <div class="card-footer">              
                    <div class="row text-center partners d-flex flex-wrap align-items-center">

                        <?php if($img_partner_1): ?>
                            <div class="col">
                                <img id="partner-1" src="<?= $img_partner_1?>">
                            </div>
                        <?php endif; ?>
                        <?php if($img_partner_2): ?>
                            <div class="col">
                                <img id="partner-2" src="<?= $img_partner_2?>">
                            </div>
                        <?php endif; ?>
                        <?php if($img_partner_3): ?>
                            <div class="col">
                                <img id="partner-3" src="<?= $img_partner_3?>">
                            </div>
                        <?php endif; ?>

                    </div>
                    <div class="row text-center">
                        <div class="col pt-3">
                            <?php if(!empty($footerText)): ?>
                                <small class="text-black-50"><?= $footerText ?></small>
                            <?php endif; ?>
                        </div>
                    </div>                          
                </div>                
            </div>
        </div>
    </div>
<script>

    $(function() {
        'use strict';

        // Detect if is IE
        var isIE = !!document.documentMode;

        var inputs = $("#access-code-digit-group").find("input");
        var input_count = inputs.length - 1; // reduced by one hidden input field
        var body = $('body');
        var code_array = [];

        inputs.first().focus();

        //  Automatically go to next input after entering alphanumerical keys
        function goToNextInput(e) {
            
            var key = e.which,
            t = $(e.target),
            sib = t.next('input');

            //  Stop at last element
            if(sib.length == 0) {
                e.preventDefault();
                //console.log("Last element reached");
                return false;
            }

            //  Check if key is alphanumerical
            if (key != 9 && (key < 48 || key > 57) && ( key < 64 || key > 91 ) && (key < 96 || key > 123) ) {
                e.preventDefault();
                return false;
            }
            
            if (!sib || !sib.length) {
                sib = body.find('input').eq(0);
            }

            // ensure only to go next if value has been entered into input
            if(t.val() != "") {                
                sib.select().focus();
            }
        }

        //  Filter for alphanumerical input keys and adjust tabbing & backspace behaviour
        function onKeyDown(e) {

            var key = e.which,
            t = $(e.target),
            pre = t.prev("input");

            /* Delete on tab back */
            if(event.shiftKey && key == 9) {   
                pre.val("");
                return true;
            }

            /* delete on backspace */
            if(key == 8) {
                if(t.val() == "" ) {
                    pre.val("").select();                    
                } 
                else {
                    t.val("");
                }
                t.trigger("input");
                return true;
            }

            if(event.controlKey && key == 86) {
                return true
            }

            if ((key >= 48 && key <= 57) || ( key > 64 && key < 91 ) || (key > 96 && key < 123) ) {
            return true;
            }

            e.preventDefault();
            return false;
        }
        
        function onFocus(e) {
            $(e.target).select();
        }

        function onInput(e) {
            var t,pos, value;
            t = e.target;
            value = t.value;
            pos = t.dataset.pos;
            code_array[pos-1] = value;
            if(value == "") {
                code_array.pop();
            }

            $("#alert-error").hide();
            checkIfValid();
        }

        function onPaste(e) {

            var pastedData;

            // access the clipboard using the api and generate array
            if(isIE) {
                pastedData = window.clipboardData.getData('text')
            } else {
                pastedData = e.originalEvent.clipboardData.getData('text');
            }         
           
            //var pastedArray = Array.from(pastedData);
            var pastedArray = pastedData.split("");
            
            if(pastedArray.length <= input_count) {                
                
                code_array = pastedArray;

                pastedArray.forEach(function(element, index) {
                    var id = index + 1;                
                    $("#access-digit-"+id).val(element);                
                });

                $("input.form-control").last().focus();
                checkIfValid();
            }
        }

        //  Check if all input fields have been entered
        function checkIfValid() {        

            // Count array length without empty elements
            var arrayLengthNotEmpty = code_array.filter(Boolean).length;

            if( arrayLengthNotEmpty == input_count) {
                $("#submit-btn").prop( "disabled", false );
                $("#alert-info").hide();
                $("#alert-ready").fadeIn();
                $("#code").val(code_array.join(""));
                $("#code_redirect").val(code_array.join(""));
            } else {
                $("#submit-btn").prop( "disabled", true );
                $("#alert-ready").hide();
                $("#alert-info").fadeIn();
            }
        }

        function toggleCodeSelection(e){
            $("#main-wrap").toggle();
            $("#selection-wrap").toggle();  
        }

        body.on('click', '#btn-open-select-code', toggleCodeSelection);
        body.on('click', '#btn-close-select-code', toggleCodeSelection);

        //  Event Listeners

        body.on('keyup', 'input', goToNextInput);
        body.on('keydown', 'input', onKeyDown);
        body.on('click', 'input', onFocus);
        body.on('input', 'input', onInput);
        body.on('paste', 'input', onPaste);


        $('form[name=access-code-form]').submit(function(e){
            e.preventDefault();

            $("input.form-control").prop("disabled", true);
            $("#submit-btn").prop( "disabled", true );
            $("#alert-ready").hide();
            $("#alert-error").hide();
            $(".not-loading").hide();
            $(".loading").css("display", "inline-block");

            $.ajax({
                type: 'POST',
                cache: false,
                url: '<?php echo APP_PATH_SURVEY_FULL ?>',
                data: 'id=access_code_form_send&'+$(this).serialize(), 
                success: function(response) {
                    setTimeout(function() {
                        // Dirty solution since there is no API-Endpoint to check if code is valid
                        if( $(response).find('#surveytitlelogo').length >= 1 ) {
                            /* Trigger redirect form on success */
                            $("#alert-success").fadeIn();
                            $(".loading").hide();
                            $(".connecting").show();
                            $("#redirect_form").submit();
                        } else if($(response).find("#survey_code_form").length >= 1) {
                            /* Reset form if code is not valid */
                            $("#alert-error").fadeIn();
                            $("#alert-info").fadeIn();
                            $("input.form-control").val("").prop("disabled", false);
                            code_array = [];
                            $("input.form-control").first().focus();
                            checkIfValid();
                            $(".loading").hide();
                            $(".not-loading").show();
                        } else {
                            //   Fallback if none works
                            $("#redirect_form").submit();
                            $(".loading").hide();
                            $(".not-loading").show();
                        }
                    }, 1250); // simulate default loading time for UX

                    },
                error: function(err) {
                    console.log(err);
                }
            });        
        });
    })


</script>
<style>

    /* 
    *   Fix default layouts
    *
    */

    body {
        background: url(<?php echo $module->getAnyImage64("image")?>) no-repeat center center fixed;
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
    
    #container {        
        /* Equalize padding-left */
        padding: 5px 15px 0px 5px;
    }

    button:focus, button:hover {
        outline:0;
    }

    /*
    *   Enhanced Card View
    *   
    */

    .page-wrapper {
        min-height: 100vh;
    }

    .wrapper {
        margin: 0 auto;

    }

    .wrapper-w580 {
        max-width: 580px;
    }

    .card-shadow {
        box-shadow: 0 10px 20px rgba(0,0,0,0.19), 0 6px 6px rgba(0,0,0,0.23);
    }

    .card-header #survey-logo {
        max-width:100%;
        padding:25px;
    }

    .card-body #alert-info {
        border-color: #ecf0f1 !important; /* gets overwritten in style.css */
        background-color: #ecf0f1;
        color: #222222;
    }

    .card-body #alert-ready, .card-body #alert-success {
        color: #155724 !important; /* gets overwritten in style.css  */
        background-color: #d4edda !important; /* gets overwritten in style.css  */
        border-color: #c3e6cb !important; /* gets overwritten in style.css  */
    }

    .card-body #access-code-digit-group input {
        max-width: 6rem;
        font-size: 2.5rem;
        text-align: center;
        line-height: 80px;
        height: 5rem;
        font-family: "Courier New";
    }

    .card-body #access-code-digit-group input:focus {
        background: none;
    }

    .card-body .form-control.stacked {
        padding:0;
    }

    .card-body .form-control:focus {
        outline: 0 !important;
        box-shadow: none;        
    }

    .card-footer .partners img {
        max-width: 150px;
        padding: 5px;
    }

    /*  
    *   Responsive Breakpoints / Media Queries
    *   https://getbootstrap.com/docs/4.5/layout/overview/#responsive-breakpoints
    *
    */
    @media (min-width: 768px) {
        /* Medium devices (tablets, 768px and up) */
        .wrapper {
            padding-top: 50px;
        }
    }
    
    @media (min-width: 992px) {
        /* Large devices (desktops, 992px and up) */
        .wrapper {
            padding-top: 75px;
        }        
    }
    
    @media (min-width: 1200px) {
        /* Extra large devices (large desktops, 1200px and up) */
        .wrapper {
            padding-top: 100px;
        }          
    }

    /*
    * Browser Fixes
    */
    @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
        /* IE10+ CSS */
        .card-header {
            max-height: 200px;
        }
    }

    /*
    *   Helpers
    *
    */

    .hidden {
        display: none;
    }

</style>