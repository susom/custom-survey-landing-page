<?php
/**
 * Created by PhpStorm.
 * User: andy123
 * Date: 5/16/18
 * Time: 2:00 PM
 */

namespace Stanford\CustomSurveyLandingPage;

use REDCap;

class CustomSurveyLandingPage extends \ExternalModules\AbstractExternalModule
{

    public function getAnyImage64($key) {
        $img_doc_id = $this->getProjectSetting($key);
        $path = \Files::copyEdocToTemp($img_doc_id);
        
        if($path) {
            $contents = file_get_contents($path);
            $mime = mime_content_type($path);
            return "data:" . $mime . ";base64," . base64_encode($contents);
        }

        else return false;
    }

    /**
     * Return Access Code length based on access type
     * 
     * 
    **/
    public function getAccessCodeLength($access) {

        if( $access == 'short' ) {
            return \Survey::SHORT_CODE_LENGTH;
        }
        if( $access == 'numeral' ) {
            return \Survey::ACCESS_CODE_NUMERAL_LENGTH;
        }
        else {
            return \Survey::ACCESS_CODE_LENGTH;
        }
        
    }


    /**
     * Add some context to the config page to help the user-interface
     * @param null $project_id
     * @return bool
     */
    /*
    function redcap_module_configure_button_display($project_id = null) {
        if (!empty($project_id)) {
            $publicUrl = empty($this->getPublicUrl()) ? "''" : json_encode($this->getPublicUrl());
            $shortUrl = empty($this->getShortUrl()) ? "''" : json_encode($this->getShortUrl());
        }

	?>
            <script type="text/javascript">
		var CSLP = CSLP || {};
                CSLP.surveyUrl = <?php echo $publicUrl ?>;
                CSLP.surveyShortUrl = <?php echo $shortUrl ?>;
            </script>
            <style>
                code.selectOnClick { cursor: pointer; }
            </style>
        <?php
        return true;
    }
*/

    public function redcap_module_save_configuration($project_id = null) {
        if (empty($project_id)) return;

        // Check to see if they have asked us to regenerated the short URL
        $this->checkClearShortUrl();
    }


    /**
     * Generate a url to the custom survey entry page
     * @return string
     */
    private function getPublicUrl() {
        global $auth_meth;
        $useApiUrl = $this->getProjectSetting('use-api-url');
        $is_above_843 = REDCap::versionCompare(REDCAP_VERSION, '8.4.3') >= 0;
        $url = ( ($auth_meth === "shibboleth" && $is_above_843 ) || $useApiUrl)  ? $this->getUrl("survey.php", true, true) : $this->getUrl("survey.php");
        return $url;
    }

    private static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    private function getShortUrl() {
        $shortUrl = $this->getProjectSetting('short-url');
        if (empty($shortUrl)) {
            // Try to make one
            $publicUrl = $this->getPublicUrl();
            // $publicUrl = str_replace("localhost","redcap.stanford.edu",$publicUrl);
            $result = getREDCapShortUrl($publicUrl);

            if (isset($result['errorMessage'])) {
                $shortUrl = false;
            } else {
                $shortUrl = $result['url_short'];
                $this->setProjectSetting('short-url', $shortUrl);
            }
        }
        return $shortUrl;
    }


    /** See if we need to reset the short url for this project */
    private function checkClearShortUrl() {
        if ($this->getProjectSetting('clear-short-url')) {
            $this->setProjectSetting('short-url',"");
            $this->getShortUrl();
            $this->setProjectSetting('clear-short-url',0);
        }
    }


    function redcap_every_page_top($project_id = null) {

        if (PAGE === "DataEntry/index.php") {
            ?>
                <script>
                    // Wait for the url to appear on the Survey Access Code page
                    waitForUrl = function(selector, callback) {
                        if (jQuery(selector).text().length) {
                            callback();
                        } else {
                            setTimeout(function() {
                                waitForUrl(selector, callback);
                            }, 100);
                        }
                    };

                    // Override the getAccessCode with an anonymous function
                    (function () {
                        waitForUrl('textarea.staticInput', function() {

                            // work the magic
                            var shortUrl = <?php echo json_encode($this->getShortUrl()) ?>;
                            var publicUrl = <?php echo json_encode($this->getPublicUrl()) ?>;

                            function getUrlDiv(url, id, name) {
                                $d = '<div id="' + id + '" style="font-size:12px;padding:10px 0 10px;">' +
                                        '<div style="font-weight:bold;font-size:12px;line-height:1.8;margin-left:5px;">' + name + '</div>' +
                                        '<input id="' + id + '" value="' + url + '" onclick="this.select();" readonly="readonly" class="staticInput" style="width:90%;max-width:600px;margin-bottom:5px;margin-right:5px;">' +
                                    '</div>';
                                return $($d);
                            }

                            var ta = $('textarea.staticInput');
                            ta.after(getUrlDiv(shortUrl, 'custShortUrl', 'Custom Short URL'));
                            ta.after('<div style="padding:0px 0px 4px 10px;color:#444;font-size:12px;line-height:1.8;">OR:</div>');
                            ta.after(getUrlDiv(publicUrl,'custPubUrl', 'Custom Long URL'));
                            ta.after('<div style="padding:15px 0px 0px 10px;color:#444;font-size:12px;line-height:1.8;">OR, use your Custom Survey Landing Page EM URLs:</div>');
                        });
                    })();
                </script>
            <?php
        }

        if (PAGE === "Surveys/invite_participants.php") {
            ?>
                <script>

                    // HTML Elements
                    var separator = '<fieldset style="margin:25px 0 0;padding:10px 10px 6px;border:0;border-top:1px solid #ccc;font-weight:normal;"><legend style="padding:0 3px;margin-left:10px;color:#666;font-size:15px;">OR</legend></fieldset>';
                    var alert = '<div class="mt-3 alert alert-info" style="font-size:13px;border-color: #bee5eb !important;font-weight:normal;"><i class="fas fa-info-circle"></i> This URL has been generated by Custom Survey Landing Page EM.</div>';

                    // URL Insertion for Participant List Tab

                    // Wait for the url to appear on the Survey Access Code page
                    waitForUrl = function(selector, callback) {
                        if (jQuery(selector).length) {
                            callback();
                        } else {
                            setTimeout(function() {
                                waitForUrl(selector, callback);
                            }, 100);
                        }
                    };

                    // Override the getAccessCode with an anonymous function
                    (function () {
                        waitForUrl('textarea.staticInput', function() {

                            // work the magic
                            var shortUrl = <?php echo json_encode($this->getShortUrl()) ?>;
                            var publicUrl = <?php echo json_encode($this->getPublicUrl()) ?>;

                            // Get insert point:
                            var ta = $('textarea.staticInput');

                            function getUrlDiv(url, id, name) {
                                $d = '<div style="font-size:12px;line-height:1.8;margin-left:5px;margin-top:15px;">' + name + '</div>' +
                                     '<textarea id="' + id + '" class="staticInput" style="margin-left:22px;white-space:normal;color:#111;font-size:16px;width:420px;" readonly="readonly" onclick="this.select();">' + 
                                     ''+ url +'' +
                                     '</textarea>' + 
                                     '</div>';
                                return $($d);
                            }

                            // Append elements

                            // Append short URL only if has been generated                            
                            ta.after(alert);

                            if(shortUrl != false) {
                                ta.after(getUrlDiv(shortUrl, 'custShortUrl', 'Custom Short URL'));
                            }

                            ta.after(getUrlDiv(publicUrl,'custPubUrl', 'Custom Long URL'));
                            ta.after('<div style="margin:10px 0 8px;">1.)  Go to Custom Survey Landing Page:</div>')
                            ta.after(separator);

                        });
                    })();

                    // URL Insertion for Public Survey Tab

                    // Wait for the url to appear on the Survey Access Code page
                    waitForElement = function(selector, callback) {
                        if (jQuery(selector).length) {
                            callback();
                        } else {
                            setTimeout(function() {
                                waitForElement(selector, callback);
                            }, 100);
                        }
                    };

                    // Override the getAccessCode with an anonymous function
                    (function () {

                        // Add some custom JS to update the Access Code page with the alternate URLs
                        waitForElement('#longurl', function() {
                            // work the magic
                            var shortUrl = <?php echo json_encode($this->getShortUrl()) ?>;
                            var publicUrl = <?php echo json_encode($this->getPublicUrl()) ?>;

                            // Get insert point:
                            var ta = $('#shorturl_div');

                            function getUrlDiv(url, id, name) {

                                $d= '<!-- ' + name +' -->' +
                                    '<div style="padding:5px 0px 6px;">' +
                                    '<div style="float:left;font-weight:bold;font-size:12px;line-height:1.8;">' + name +':</div>' +
                                    '<input id="' + id + '" value="' + url +'" onclick="this.select();" readonly="readonly" class="staticInput" style="float:left;width:80%;max-width:400px;margin-bottom:5px;margin-right:5px;">' +
                                    '<button class="btn btn-defaultrc btn-xs btn-clipboard" title="Copy to clipboard" data-clipboard-target="#' + id + '" style="padding:3px 8px 3px 6px;"><i class="fas fa-paste"></i></button>' +
                                    '</div>' +
                                    '<div class="clear"></div>' + 
                                    alert;


                                return $($d);
                            }

                            //  Append elements

                            ta.before(separator);
                            ta.before(getUrlDiv(publicUrl,'custPubUrl', 'Custom Long URL '));

                            // Append short URL only if has been generated
                            if(shortUrl != false) {
                                ta.before(separator);
                                ta.before(getUrlDiv(shortUrl, 'custShortUrl', 'Custom Short URL'));
                            }

                        });
                    })();

                </script>
            <?php
        }
    }

}
