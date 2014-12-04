<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

//view posts
$object_id = (int) w2PgetParam($_GET, 'forum_id', 0);

$message_id = (int) w2PgetParam($_GET, 'message_id', 0);
$post_message = (int) w2PgetParam($_GET, 'post_message', 0);
$f = w2PgetParam($_POST, 'f', 0);

// check permissions
$perms = &$AppUI->acl();
$canAuthor = canAdd('forums');
$canDelete = canDelete('forums', $object_id);
$canRead = $perms->checkModuleItem('forums', 'view', $object_id);
$canEdit = $perms->checkModuleItem('forums', 'edit', $object_id);
$canAdminEdit = canEdit('system');

if (!$canRead) {
    $AppUI->redirect(ACCESS_DENIED);
}

$message = new CForum_Message();
$message->load($message_id);

if (0 == $object_id) {
    $object_id = $message->message_forum;
}

$object = new CForum();
$object->load($object_id);

$project = new CProject();
$project->load($object->forum_project);

if (!$object) {
    $AppUI->setMsg('Forum');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Forum', 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
if ($message_id) {
    $titleBlock->addCrumb('?m=forums&a=view&forum_id=' . $object_id, 'topics for this forum');
}
if ($canEdit) {
    $titleBlock->addCrumb('?m=forums&a=addedit&forum_id=' . $object_id, 'edit this forum');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete forum', true, $msg);
    }
}
$titleBlock->addCell(arraySelect($filters, 'f', 'size="1" class="text" onchange="document.filterFrm.submit();"', $f, true), '', '<form action="?m=forums&a=view&forum_id=' . $object_id . '" method="post" name="filterFrm" accept-charset="utf-8">', '</form>');
$titleBlock->show();

$view = new \Web2project\Controllers\View($AppUI, $object, 'Forum');
echo $view->renderDelete();

include $AppUI->getTheme()->resolveTemplate('forums/view');

echo $AppUI->getTheme()->styleRenderBoxBottom();
if ($post_message) {
    include (W2P_BASE_DIR . '/modules/forums/post_message.php');
} else {
    if ($message_id) {
        include (W2P_BASE_DIR . '/modules/forums/view_messages.php');
    } else {
        include (W2P_BASE_DIR . '/modules/forums/view_topics.php');
    }
}
