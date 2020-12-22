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

$access_code_length = \Survey::ACCESS_CODE_LENGTH;

// Show the results

?>
<div class="page-wrapper">
        <div class="wrapper wrapper-w580">
            <div class="card card-shadow">
                <div class="card-body pt-3 pb-5">
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
                                    <span class="loading spinner-border hidden" role="status" aria-hidden="true"></span>
                                    <span class="loading hidden"><?=$module->tt("checking")?></span>
                                </button>                        
                            </div>
                        </form>
                        <!-- hidden form to send the actual request for redirect -->
                        <form id="redirect_form" method="POST" action="<?php echo APP_PATH_SURVEY_FULL ?>">
                            <input name="code" id="code_redirect" type="hidden">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>

    $(function() {
        'use strict';

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
            // access the clipboard using the api and generate array
            var pastedData = e.originalEvent.clipboardData.getData('text');
            var pastedArray = Array.from(pastedData);
            
            if(pastedArray.length <= input_count) {                
                
                code_array = pastedArray;

                pastedArray.forEach((element, index) => {
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

        //  Event Listeners

        body.on('keyup', 'input', goToNextInput);
        body.on('keydown', 'input', onKeyDown);
        body.on('click', 'input', onFocus);
        body.on('input', 'input', onInput);
        body.on('paste', 'input', onPaste);


        $('form[name=access-code-form]').submit(function(e){
            e.preventDefault();

            $("input.form-control").prop("disabled", true);
            $("#alert-ready").hide();
            $(".not-loading").hide();
            $(".loading").css("display", "inline-block");

            $.ajax({
                type: 'POST',
                cache: false,
                url: '<?php echo APP_PATH_SURVEY_FULL ?>',
                data: 'id=access_code_form_send&'+$(this).serialize(), 
                success: function(response) {
                    console.log(response);
                    setTimeout(() => {
                        // Dirty solution since there is no API-Endpoint to check if code is valid
                        //var $result = $(response).find('#surveytitlelogo')
                        if( $(response).find('#surveytitlelogo').length >= 1 ) {
                            /* Trigger redirect form on success */                            
                            $("#redirect_form").submit();
                        } else if($(response).find("#survey_code_form").length >= 1) {
                            /* Reset form if code is not valid */
                            $("#alert-error").fadeIn();
                            $("#alert-info").fadeIn();
                            $("input.form-control").val("").prop("disabled", false);
                            code_array = [];
                            $("input.form-control").first().focus();
                            checkIfValid();
                        } else {
                            //   Fallback if none works
                            $("#redirect_form").submit();
                        }
                        $(".loading").hide();
                        $(".not-loading").show();
                    }, 1500); // simulate default loading time for UX

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

    .card-body #alert-info {
        border-color: #ecf0f1 !important; /* gets overwritten in style.css */
        background-color: #ecf0f1;
        color: #222222;
    }

    .card-body #alert-ready {
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
    *   Helpers
    *
    */

    .hidden {
        display:none;
    }

</style>