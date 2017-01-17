<!DOCTYPE html>
<html lang="en">
<head>
<title>RollCall</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<style type="text/css">
    @import url(http://fonts.googleapis.com/css?family=Lato);
    /* CLIENT-SPECIFIC STYLES */
    #outlook a{padding:0;} /* Force Outlook to provide a "view in browser" message */
    .ReadMsgBody{width:100%;} .ExternalClass{width:100%;} /* Force Hotmail to display emails at full width */
    .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing */
    body, table, td, a{-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;} /* Prevent WebKit and Windows mobile changing default text sizes */
    table, td{mso-table-lspace:0pt; mso-table-rspace:0pt;} /* Remove spacing between tables in Outlook 2007 and up */
    img{-ms-interpolation-mode:bicubic;} /* Allow smoother rendering of resized image in Internet Explorer */
    /* RESET STYLES */
    body{margin:0; padding:0; background-color: #EFECE8;}
    img{border:0; height:auto; line-height:100%; outline:none; text-decoration:none;}
    table{border-collapse:collapse !important;}
    body{height:100% !important; margin:0; padding:0; width:100% !important;}
    /* iOS BLUE LINKS */
    .appleBody a {color:#68440a; text-decoration: none;}
    .appleFooter a {color:#999999; text-decoration: none;}
    /* MOBILE STYLES */
    @media screen and (max-width: 525px) {
        /* ALLOWS FOR FLUID TABLES */
        table[class="wrapper"]{
          width:100% !important;
        }
        /* ADJUSTS LAYOUT OF LOGO IMAGE */
        td[class="logo"]{
          text-align: left;
          padding: 20px 0 20px 0 !important;
        }
        td[class="logo"] img{
          margin:0 auto!important;
        }
        /* USE THESE CLASSES TO HIDE CONTENT ON MOBILE */
        td[class="mobile-hide"]{
          display:none;}
        img[class="mobile-hide"]{
          display: none !important;
        }
        img[class="img-max"]{
          max-width: 100% !important;
          width: 100% !important;
          height:auto !important;
        }
        /* FULL-WIDTH TABLES */
        table[class="responsive-table"]{
          width:100%!important;
        }
        /* UTILITY CLASSES FOR ADJUSTING PADDING ON MOBILE */
        td[class="padding"]{
          padding: 10px 5% 15px 5% !important;
        }
        td[class="padding-copy"]{
          padding: 10px 5% 10px 5% !important;
          text-align: center;
        }
        td[class="padding-meta"]{
          padding: 30px 5% 0px 5% !important;
          text-align: center;
        }
        td[class="no-pad"]{
          padding: 0 0 20px 0 !important;
        }
        td[class="no-padding"]{
          padding: 0 !important;
        }
        td[class="section-padding"]{
          padding: 50px 15px 50px 15px !important;
        }
        td[class="section-padding-bottom-image"]{
          padding: 50px 15px 0 15px !important;
        }
        /* ADJUST BUTTONS ON MOBILE */
        td[class="mobile-wrapper"]{
            padding: 10px 5% 15px 5% !important;
        }
        td[class="devices-deployment-name"]{
            font-size: 16px !important;
            padding-top: 3% !important;
        }
        table[class="mobile-button-container"]{
            margin:0 auto;
            width:100% !important;
        }
        a[class="mobile-button"]{
            width:80% !important;
            padding: 15px !important;
            border: 0 !important;
            font-size: 16px !important;
        }
    }
    @media screen and (max-width: 375px) {
        td[class="devices-deployment-name"]{
            display: none;
            font-size: 13px !important;
            padding-top: 2% !important;
        }
    }
</style>
</head>
<body style="margin: 0; padding: 0;">

<!-- HEADER -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="#EFECE8">
            <!-- HIDDEN PREHEADER TEXT -->
            <div style="display: none; font-size: 1px; color: #4A4A4A; line-height: 1px; font-family: Lato, Helvetica, Arial, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
                    {{ $msg }}
            </div>
            <div align="center" style="padding: 0px 15px 0px 15px;">
                <table border="0" cellpadding="0" cellspacing="0" width="500" class="wrapper">
                    <!-- LOGO/PREHEADER TEXT -->
                    <tr>
                        <td style="padding: 20px 0px 30px 0px;" class="logo">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td bgcolor="#EFECE8" width="100" align="left">
                                        <a href="#" target="_blank">
                    <!-- XXX: Currently grabs this from the PL's site -->
                                            <img alt="Logo" src="http://github.ushahidi.org/rollcall-pattern-library/assets/img/rollcall-wordmark-full_color.png" width="155" height="40" style="display: block; font-family: Lato, Helvetica, Arial, sans-serif; color: #4A4A4A; font-size: 16px;" border="0">
                                        </a>
                                    </td>
                                    <td bgcolor="#EFECE8" width="400" align="right" class="mobile-hide">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="right" style="padding: 0 0 5px 0; font-size: 14px; font-family: Lato, Arial, sans-serif; color: #666666; text-decoration: none;"><span style="color: #4A4A4A; text-decoration: none;">
                    {{ $org_subdomain }}.rollcall.io
</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<!-- ONE COLUMN SECTION -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="#FBF9F6" align="center" style="padding: 35px 15px 35px 15px;" class="section-padding">
            <table border="0" cellpadding="0" cellspacing="0" width="500" class="responsive-table">
                <tr>
                    <td>
                        <!-- ROLLCALL -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" style="border: 1px solid #E6E6DD; box-shadow: 0 1px 3px rgba(0,0,0,.16);">
                            <tr>
                    <td align=center style="text-align: center; padding: 20px 20px 10px;"><img src="http://www.gravatar.com/avatar/{{ $gravatar }}?d=identicon&s=40" alt="{{ $author }}" style="display: inline-block; color: #666666;  font-family: Lato, Helvetica, arial, sans-serif; font-size: 16px; border-radius: 50%;" class="img-max"></td>

                    <td class="devices-deployment-name" style="display: none; vertical-align:top; text-align: left; padding: 8.5% 65% 0 29%; font-size: 9px; font-weight: 700; font-family: Lato, Helvetica, Arial, sans-serif; color: #fff;"> {{ $author }}</td>
                            </tr>
                            <tr>
                                <td class="padding-copy">
                                    <!-- MESSAGE -->
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                    <td align="center" style="font-size: 25px; font-style: italic; font-family: Lato, Helvetica, Arial, sans-serif; color: #333333; padding: 0 20px 20px;" class="padding-copy"> {{ $msg }}</td>
                                        </tr>
                                        @if (count($answers) > 0)
                                            <tr>
                                                <td align="center" style="padding: 0 20px 20px; font-size: 16px; line-height: 25px; font-family: Lato, Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">Please select your answer below or reply directly to this email and include "{{ $answers[0] }}," or "{{ $answers[1] }}," in your message.</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td align="center" style="padding: 0 20px 20px; font-size: 16px; line-height: 25px; font-family: Lato, Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">Please reply <a href="{{ $answer_url }}">on Rollcall</a> or respond directly to this email.</td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                            @if (count($answers) > 0)
                            <tr>
                                <td align="center">
                                    <!-- BULLETPROOF BUTTON -->
                                    <table width="100%" border="0" cellspacing="0" cellpadding="10" bgcolor="#EFEFEB" class="mobile-button-container" style="border-top: 1px solid #E6E6DD;">
                                        <tr>
                                            <td align="center" class="padding-copy" style="padding: 20px;">
                                                <table border="0" cellspacing="0" cellpadding="0" class="responsive-table">
                                                    <tr>
                                                        <td align="center"><a href="{{ $answer_url_no }}" target="_blank" style="width: 70px; height: 20px; font-size: 16px; font-family: Lato, Helvetica, Arial, sans-serif; font-weight: normal; color: #ffffff; text-decoration: none; background-color: #e8bb4a; border-top: 35px solid #e8bb4a; border-bottom: 35px solid #e8bb4a; border-left: 10px solid #e8bb4a; border-right: 10px solid #e8bb4a; border-radius: 90px; -webkit-border-radius: 90px; -moz-border-radius: 90px; display: inline-block; margin: 0 10px;" class="mobile-button">{{ $answers[0] }}</a></td>

                                                        <td align="center"><a href="{{ $answer_url_yes }}" target="_blank" style="width: 70px; height: 20px; font-size: 16px; font-family: Lato, Helvetica, Arial, sans-serif; font-weight: normal; color: #ffffff; text-decoration: none; background-color: #64b269; border-top: 35px solid #64b269; border-bottom: 35px solid #64b269; border-left: 10px solid #64b269; border-right: 10px solid #64b269; border-radius: 90px; -webkit-border-radius: 90px; -moz-border-radius: 90px; display: inline-block; margin: 0 10px;" class="mobile-button">{{ $answers[1] }}</a></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- FOOTER -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="#EFECE8" align="center" style="padding: 20px 0px;">
            <!-- UNSUBSCRIBE COPY -->
            <table width="500" border="0" cellspacing="0" cellpadding="0" align="center" class="responsive-table">
                <tr>
                    <td align="center" style="font-size: 12px; line-height: 18px; font-family: Lato, Helvetica, Arial, sans-serif; color:#666666;">
                        <span class="appleFooter" style="color:#666666;">RollCall</span><br><a href="http://rollcall.io" class="original-only" style="color: #666666; text-decoration: none;">www.rollcall.io</a><span class="original-only" style="font-family: Lato, Arial, sans-serif; font-size: 12px; color: #444444;">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
