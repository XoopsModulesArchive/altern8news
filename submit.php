<?php

// $Id: submit.php,v 1.2 2006/03/18 16:41:35 mikhail Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://xoops.codigolivre.org.br>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
require dirname(__DIR__, 2) . '/mainfile.php';
require_once __DIR__ . '/class/class.newsstory.php';
if (empty($xoopsModuleConfig['anonpost']) && !is_object($xoopsUser)) {
    redirect_header('index.php', 0, _NOPERM);

    exit();
}
$op = 'form';
foreach ($_POST as $k => $v) {
    ${$k} = $v;
}
if (isset($_POST['preview'])) {
    $op = 'preview';
} elseif (isset($_POST['post'])) {
    $op = 'post';
}
switch ($op) {
case 'preview':
          $myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object
    $xt = new XoopsTopic($xoopsDB->prefix('temen'), $_POST['topic_id']);
    include XOOPS_ROOT_PATH . '/header.php';
    $p_subject = htmlspecialchars($subject, ENT_QUOTES | ENT_HTML5);
    if ($xoopsUser && $xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
        $nohtml = isset($nohtml) ? (int)$nohtml : 0;
    } else {
        $nohtml = 1;
    }
    $html = empty($nohtml) ? 1 : 0;
    if (isset($nosmiley) && (int)$nosmiley > 0) {
        $nosmiley = 1;

        $smiley = 0;
    } else {
        $nosmiley = 0;

        $smiley = 1;
    }
    $p_message = $myts->previewTarea($message, $html, $smiley, 1);
    $subject = htmlspecialchars($subject, ENT_QUOTES | ENT_HTML5);
    $message = htmlspecialchars($message, ENT_QUOTES | ENT_HTML5);
    $noname = isset($noname) ? (int)$noname : 0;
    $notifypub = isset($notifypub) ? (int)$notifypub : 0;
    $p_message = ('' != $xt->topic_imgurl()) ? '<img src="images/topics/' . $xt->topic_imgurl() . '" align="right" alt="">' . $p_message : $p_message;
    themecenterposts($p_subject, $p_message);
    require __DIR__ . '/include/storyform.inc.php';
    require XOOPS_ROOT_PATH . '/footer.php';

          break;
case 'post':
          $nohtml_db = 1;
    if ($xoopsUser) {
        $uid = $xoopsUser->getVar('uid');

        if ($xoopsUser->isAdmin($xoopsModule->mid())) {
            $nohtml_db = empty($nohtml) ? 0 : 1;
        }
    } else {
        if (1 == $xoopsModuleConfig['anonpost']) {
            $uid = 0;
        } else {
            redirect_header('index.php', 3, _NW_ANONNOTALLOWED);

            exit();
        }
    }
    $story = new NewsStory();
    $story->setTitle($subject);
    $story->setHometext($message);
    $story->setUid($uid);
    $story->setTopicId($topic_id);
    $story->setHostname(xoops_getenv('REMOTE_ADDR'));
    $story->setNohtml($nohtml_db);
    $nosmiley = isset($nosmiley) ? (int)$nosmiley : 0;
    $notifypub = isset($notifypub) ? (int)$notifypub : 0;
    $story->setNosmiley($nosmiley);
    $story->setNotifyPub($notifypub);
    $story->setType('user');
    if (1 == $xoopsModuleConfig['autoapprove']) {
        $approve = 1;

        $story->setApproved($approve);

        $story->setPublished(time());

        $story->setExpired(0);

        $story->setTopicalign('R');
    }
    $result = $story->store();
    if ($result) {
        // Notification

        $notificationHandler = xoops_getHandler('notification');

        $tags = [];

        $tags['STORY_NAME'] = $subject;

        $tags['STORY_URL'] = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/article.php?storyid=' . $story->storyid();

        if (1 == $xoopsModuleConfig['autoapprove']) {
            $notificationHandler->triggerEvent('global', 0, 'new_story', $tags);
        } else {
            $tags['WAITINGSTORIES_URL'] = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/admin/index.php?op=newarticle';

            $notificationHandler->triggerEvent('global', 0, 'story_submit', $tags);
        }

        // If notify checkbox is set, add subscription for approve

        if ($notifypub) {
            require_once XOOPS_ROOT_PATH . '/include/notification_constants.php';

            $notificationHandler->subscribe('story', $story->storyid(), 'approve', XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE);
        }

        /*
            if ($xoopsModuleConfig['notifysubmit'] == 1 ) {
                $xoopsMailer =& getMailer();
                $xoopsMailer->useMail();
                $xoopsMailer->setToEmails($xoopsConfig['adminmail']);
                $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
                $xoopsMailer->setFromName($xoopsConfig['sitename']);
                $xoopsMailer->setSubject(_NW_NOTIFYSBJCT);
                $body = _NW_NOTIFYMSG;
                $body .= "\n\n"._NW_TITLE.": ".$story->title();
                $body .= "\n"._POSTEDBY.": ".XoopsUser::getUnameFromId($uid);
                $body .= "\n"._DATE.": ".formatTimestamp(time(), 'm', $xoopsConfig['default_TZ']);
                $body .= "\n\n".XOOPS_URL.'/modules/altern8news/admin/index.php?op=edit&storyid='.$result;
                $xoopsMailer->setBody($body);
                $xoopsMailer->send();
            }
        */
    } else {
        echo 'error';
    }
    redirect_header('index.php', 2, _NW_THANKS);

          break;
case 'form':

          default:
          $xt = new XoopsTopic($xoopsDB->prefix('temen'));
    require XOOPS_ROOT_PATH . '/header.php';
    $subject = '';
    $message = '';
    $noname = 0;
    $nohtml = 0;
    $nosmiley = 0;
    $notifypub = 1;
    require __DIR__ . '/include/storyform.inc.php';
    require XOOPS_ROOT_PATH . '/footer.php';

          break;
}
