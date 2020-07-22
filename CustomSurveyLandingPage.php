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

    public function getImageUrl() {
        $img_doc_id = $this->getProjectSetting('image');
        $img_path = \Files::copyEdocToTemp($img_doc_id);
        return $img_path;
    }

    public function getImage64() {
        $path = $this->getImageUrl();
        $contents = file_get_contents($path);
        $mime = mime_content_type($path);
        return "data:" . $mime . ";base64," . base64_encode($contents);
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
            $result = \Survey::getCustomShortUrl($publicUrl, false);

            if (self::startsWith($result, "Error:")) {
                $shortUrl = false;
            } else {
                $shortUrl = $result;
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
                                        '<input id="' + id + '" value="' + url + '" onclick="this.select();" readonly="readonly" class="staticInput" style="float:left;width:80%;max-width:230px;margin-bottom:5px;margin-right:5px;">' +
                                    '</div>';
                                return $($d);
                            }

                            var ta = $('textarea.staticInput');
                            ta.after(getUrlDiv(shortUrl, 'custShortUrl', 'Custom Short URL'));
                            ta.after('<div style="padding:0px 0px 4px 10px;color:#444;font-size:12px;line-height:1.8;">OR:</div>');
                            ta.after(getUrlDiv(publicUrl,'custPubUrl', 'Custom Long URL'));
                            ta.after('<div style="padding:0px 0px 4px 10px;color:#444;font-size:12px;line-height:1.8;">OR, use your Custom Survey Landing Page EM URLs:</div>');
                        });
                    })();
                </script>
            <?php
        }

        if (PAGE === "Surveys/invite_participants.php") {
            ?>
                <script>
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

                            function getUrlDiv(url, id) {
                                $d = '<div id="' + id + '" style="font-size:12px;padding:10px 0 10px;">' +
                                        '<div style="font-weight:bold;font-size:12px;line-height:1.8;margin-left:5px;">' + name + '</div>' +
                                            '<input id="' + id + '" value="' + url +
                                            '" onclick="this.select();" readonly="readonly" class="staticInput" ' +
                                            'style="float:left;width:90%;max-width:630px;margin-bottom:5px;margin-right:5px;">' +
                                        '<button class="btn btn-defaultrc btn-xs btn-clipboard" title="Copy to clipboard" data-clipboard-target="#' + id + '" style="padding:3px 8px 3px 6px;"><i class="fas fa-paste"></i></button>' +
                                    '</div>';
                                return $($d);
                            }

                            ta.before(getUrlDiv(publicUrl,'custPubUrl', 'Custom Long URL'));
                            ta.before('<div style="padding:0px 0px 4px 10px;color:#444;font-size:12px;line-height:1.8;">OR</div>');
                            ta.before(getUrlDiv(shortUrl, 'custShortUrl', 'Custom Short URL'));
                            ta.before('<div style="padding:0px 0px 4px 10px;color:#444;font-size:12px;line-height:1.8;">' +
                                        'OR, use your Custom Survey Landing Page EM URLs:</div>"');
                        });
                    })();
                </script>
            <?php
        }
    }

}
