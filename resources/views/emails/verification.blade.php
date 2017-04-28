<html lang="en"><head>
<title>RollCall</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
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
<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tbody><tr>
        <td bgcolor="#EFECE8">
            <!-- HIDDEN PREHEADER TEXT -->
            <div style="display: none; font-size: 1px; color: #4A4A4A; line-height: 1px; font-family: Lato, Helvetica, Arial, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">

            </div>
            <div style="padding: 0px 15px 0px 15px;" align="center">
                <table class="wrapper" cellspacing="0" cellpadding="0" border="0" width="500">
                    <!-- LOGO/PREHEADER TEXT -->
                    <tbody><tr>
                        <td style="padding: 20px 0px 30px 0px;" class="logo">
                            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tbody><tr>
                                    <td bgcolor="#EFECE8" align="left" width="100">
                                        <a href="#" target="_blank">
                                          <!-- TODO: use $message->embed -->
                                            <img alt="Logo" src="http://github.ushahidi.org/rollcall-pattern-library/assets/img/rollcall-wordmark-full_color.png" style="display: block; font-family: Lato, Helvetica, Arial, sans-serif; color: #4A4A4A; font-size: 16px;" border="0" height="40" width="155">
                                        </a>
                                    </td>
                                    <td class="mobile-hide" bgcolor="#EFECE8" align="right" width="400">
                                        <table cellspacing="0" cellpadding="0" border="0">
                                            <tbody><tr>
                                                <td style="padding: 0 0 5px 0; font-size: 14px; font-family: Lato, Arial, sans-serif; color: #666666; text-decoration: none;" align="right"><span style="color: #4A4A4A; text-decoration: none;">ushahidi.rollcall.io</span></td>
                                            </tr>
                                        </tbody></table>
                                    </td>
                                </tr>
                            </tbody></table>
                        </td>
                    </tr>
                </tbody></table>
            </div>
        </td>
    </tr>
</tbody></table>

<!-- ONE COLUMN SECTION -->
<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tbody><tr>
        <td style="padding: 35px 15px 35px 15px;" class="section-padding" bgcolor="#FBF9F6" align="center">
            <table class="responsive-table" cellspacing="0" cellpadding="0" border="0" width="500">
                <tbody><tr>
                    <td>
                        <!-- HERO IMAGE -->
                        <table cellspacing="0" cellpadding="0" border="0" width="100%">
                            <tbody><tr>
                              	<td class="padding-copy" align="center">
                                  <!-- TODO: use $message->embed -->
                                    <img src="http://github.ushahidi.org/rollcall-pattern-library/assets/img/avatar-org.png" alt="Manage your account" style="display: block; color: #666666; font-family: Lato, Helvetica, arial, sans-serif; font-size: 16px;" class="img-max" border="0" height="80" width="80"></td>

                            </tr>
                            <tr>
                                <td>
                                    <!-- COPY -->
                                    <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                        <tbody><tr>
                                            <td style="font-size: 25px; font-family: Lato, Helvetica, Arial, sans-serif; color: #333333; padding-top: 30px;" class="padding-copy" align="center">Please verify your email address</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Lato, Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy" align="center">Ushahidi uses RollCall to reach people like you on any device and get quick answers to urgent questions. Verifying your email address will allow you to create an account and an organization on RollCall.</td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <!-- BULLETPROOF BUTTON -->
                                    <table class="mobile-button-container" cellspacing="0" cellpadding="0" border="0" width="100%">
                                        <tbody><tr>
                                            <td style="padding: 25px 0 0 0;" class="padding-copy" align="center">
                                                <table class="responsive-table" cellspacing="0" cellpadding="0" border="0">
                                                    <tbody><tr>
                                                        <td align="center"><a href="{{ $url }}" target="_blank" style="font-size: 16px; font-family: Lato, Helvetica, Arial, sans-serif; font-weight: normal; color: #ffffff; text-decoration: none; background-color: #222222; border-top: 15px solid #222222; border-bottom: 15px solid #222222; border-left: 25px solid #222222; border-right: 25px solid #222222; border-radius: 3px; -webkit-border-radius: 3px; -moz-border-radius: 3px; display: inline-block;" class="mobile-button">Verify Email →</a></td>
                                                    </tr>
                                                </tbody></table>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <!-- COPY -->
                                    <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                        <tbody><tr>
                                            <td style="padding: 20px 0 0 0; font-size: 14px; line-height: 25px; font-family: Lato, Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy" align="center">If you're having trouble selecting the "Verify Email" button, copy and paste the following URL into your web browser: <a href="{{ $url }}">{{ $url }}</a></td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody></table>
                    </td>
                </tr>
            </tbody></table>
        </td>
    </tr>
</tbody></table>

<!-- FOOTER -->
<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tbody><tr>
        <td style="padding: 20px 0px;" bgcolor="#EFECE8" align="center">
            <!-- UNSUBSCRIBE COPY -->
            <table class="responsive-table" cellspacing="0" cellpadding="0" border="0" align="center" width="500">
                <tbody><tr>
                    <td style="font-size: 12px; line-height: 18px; font-family: Lato, Helvetica, Arial, sans-serif; color:#666666;" align="center">
                        <span class="appleFooter" style="color:#666666;">RollCall</span><br><a href="http://rollcall.io" class="original-only" style="color: #666666; text-decoration: none;">www.rollcall.io</a><span class="original-only" style="font-family: Lato, Arial, sans-serif; font-size: 12px; color: #444444;">
                    </span></td>
                </tr>
            </tbody></table>
        </td>
    </tr>
</tbody></table>
</body></html>
