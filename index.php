<?php

// $Id: index.php,v 1.2 2006/03/18 16:41:35 mikhail Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://xoops.codigolivre.org.br>                             //
// ------------------------------------------------------------------------- //
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
$GLOBALS['xoopsOption']['template_main'] = 'news_index.html';
require XOOPS_ROOT_PATH . '/header.php';
require_once XOOPS_ROOT_PATH . '/modules/altern8news/class/class.newsstory.php';

if (isset($_GET['storytopic'])) {
    $xoopsOption['storytopic'] = (int)$_GET['storytopic'];
} else {
    $xoopsOption['storytopic'] = 0;
}
if (isset($_GET['storynum'])) {
    $xoopsOption['storynum'] = (int)$_GET['storynum'];

    if ($xoopsOption['storynum'] > 30) {
        $xoopsOption['storynum'] = $xoopsModuleConfig['storyhome'];
    }
} else {
    $xoopsOption['storynum'] = $xoopsModuleConfig['storyhome'];
}

if (isset($_GET['start'])) {
    $start = (int)$_GET['start'];
} else {
    $start = 0;
}
if (1 == $xoopsModuleConfig['displaynav']) {
    $xoopsTpl->assign('displaynav', true);

    $xt = new XoopsTopic($xoopsDB->prefix('temen'));

    ob_start();

    $xt->makeTopicSelBox(1, $xoopsOption['storytopic'], 'storytopic');

    $topic_select = ob_get_contents();

    ob_end_clean();

    $xoopsTpl->assign('topic_select', $topic_select);

    $storynum_options = '';

    for ($i = 5; $i <= 30; $i += 5) {
        $sel = '';

        if ($i == $xoopsOption['storynum']) {
            $sel = ' selected="selected"';
        }

        $storynum_options .= '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';
    }

    $xoopsTpl->assign('storynum_options', $storynum_options);
} else {
    $xoopsTpl->assign('displaynav', false);
}

$sarray = NewsStory::getAllPublished($xoopsOption['storynum'], $start, $xoopsOption['storytopic']);

$scount = count($sarray);
for ($i = 0; $i < $scount; $i++) {
    $story = [];

    $story['id'] = $sarray[$i]->storyid();

    $story['poster'] = $sarray[$i]->uname();

    if (false !== $story['poster']) {
        $story['poster'] = "<a href='" . XOOPS_URL . '/userinfo.php?uid=' . $sarray[$i]->uid() . "'>" . $story['poster'] . '</a>';
    } else {
        $story['poster'] = $xoopsConfig['anonymous'];
    }

    $story['posttime'] = formatTimestamp($sarray[$i]->published());

    $story['text'] = $sarray[$i]->hometext();

    $introcount = mb_strlen($story['text']);

    $fullcount = mb_strlen($sarray[$i]->bodytext());

    $totalcount = $introcount + $fullcount;

    $morelink = '';

    if ($fullcount > 1) {
        $morelink .= '<a href="' . XOOPS_URL . '/modules/altern8news/article.php?storyid=' . $sarray[$i]->storyid() . '';

        $morelink .= '">' . _NW_READMORE . '</a> | ';

        $morelink .= sprintf(_NW_BYTESMORE, $totalcount);

        $morelink .= ' | ';
    }

    $ccount = $sarray[$i]->comments();

    $morelink .= '<a href="' . XOOPS_URL . '/modules/altern8news/article.php?storyid=' . $sarray[$i]->storyid() . '';

    $morelink2 = '<a href="' . XOOPS_URL . '/modules/altern8news/article.php?storyid=' . $sarray[$i]->storyid() . '';

    if (0 == $ccount) {
        $morelink .= '">' . _NW_COMMENTS . '</a>';
    } else {
        if ($fullcount < 1) {
            if (1 == $ccount) {
                $morelink .= '">' . _NW_READMORE . '</a> | ' . $morelink2 . '">' . _NW_ONECOMMENT . '</a>';
            } else {
                $morelink .= '">' . _NW_READMORE . '</a> | ' . $morelink2 . '">';

                $morelink .= sprintf(_NW_NUMCOMMENTS, $ccount);

                $morelink .= '</a>';
            }
        } else {
            if (1 == $ccount) {
                $morelink .= '">' . _NW_ONECOMMENT . '</a>';
            } else {
                $morelink .= '">';

                $morelink .= sprintf(_NW_NUMCOMMENTS, $ccount);

                $morelink .= '</a>';
            }
        }
    }

    $story['morelink'] = $morelink;

    $story['adminlink'] = '';

    if ($xoopsUser && $xoopsUser->isAdmin($xoopsModule->mid())) {
        $story['adminlink'] = $sarray[$i]->adminlink();
    }

    $story['mail_link'] = 'mailto:?subject=' . sprintf(_NW_INTARTICLE, $xoopsConfig['sitename']) . '&amp;body=' . sprintf(_NW_INTARTFOUND, $xoopsConfig['sitename']) . ':  ' . XOOPS_URL . '/modules/altern8news/article.php?storyid=' . $sarray[$i]->storyid();

    $story['imglink'] = '';

    $story['align'] = '';

    if ($sarray[$i]->topicdisplay()) {
        $story['imglink'] = $sarray[$i]->imglink();

        $story['align'] = $sarray[$i]->topicalign();
    }

    $story['title'] = $sarray[$i]->textlink() . '&nbsp;:&nbsp;' . "<a href='" . XOOPS_URL . '/modules/altern8news/article.php?storyid=' . $sarray[$i]->storyid() . "'>" . $sarray[$i]->title() . '</a>';

    $story['hits'] = $sarray[$i]->counter();

    // The line below can be used to display a Permanent Link image

    // $story['title'] .= "&nbsp;&nbsp;<a href='".XOOPS_URL."/modules/altern8news/article.php?storyid=".$sarray[$i]->storyid()."'><img src='".XOOPS_URL."/modules/altern8news/images/x.gif' alt='Permanent Link'></a>";

    $xoopsTpl->append('stories', $story);

    unset($story);
}
$totalcount = NewsStory::countPublishedByTopic($xoopsOption['storytopic']);
if ($totalcount > $scount) {
    require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

    $pagenav = new XoopsPageNav($totalcount, $xoopsOption['storynum'], $start, 'start', 'storytopic=' . $xoopsOption['storytopic']);

    $xoopsTpl->assign('pagenav', $pagenav->renderNav());
} else {
    $xoopsTpl->assign('pagenav', '');
}
$xoopsTpl->assign('lang_go', _GO);
$xoopsTpl->assign('lang_on', _ON);
$xoopsTpl->assign('lang_printerpage', _NW_PRINTERFRIENDLY);
$xoopsTpl->assign('lang_sendstory', _NW_SENDSTORY);
$xoopsTpl->assign('lang_postedby', _POSTEDBY);
$xoopsTpl->assign('lang_reads', _READS);
require_once XOOPS_ROOT_PATH . '/footer.php';
