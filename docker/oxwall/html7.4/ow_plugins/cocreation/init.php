<?php
OW::getRouter()->addRoute(new OW_Route('cocreation.index', 'cocreation', "COCREATION_CTRL_Main", 'index'));
OW::getRouter()->addRoute(new OW_Route('cocreation.knowledge.room', 'cocreation/knowledge-room/:roomId', "COCREATION_CTRL_KnowledgeRoom", 'index'));
OW::getRouter()->addRoute(new OW_Route('cocreation.data.room', 'cocreation/data-room/:roomId', "COCREATION_CTRL_DataRoom", 'index'));
OW::getRouter()->addRoute(new OW_Route('cocreation.commentarium.room', 'cocreation/commentarium-room/:roomId', "COCREATION_CTRL_CommentariumRoom", 'index'));
OW::getRouter()->addRoute(new OW_Route('cocreation.room.discussion', 'cocreation/data-room/discussion/:roomId', "COCREATION_CTRL_DiscussionWrapper", 'index'));

OW::getRouter()->addRoute(new OW_Route('cocreation.data.room.list', 'cocreation/data-room-list', "COCREATION_CTRL_DataRoomList", 'index'));

//Admin area
OW::getRouter()->addRoute(new OW_Route('cocreation-settings', '/cocreation/settings', 'COCREATION_CTRL_Admin', 'settings'));
OW::getRouter()->addRoute(new OW_Route('cocreation-analysis', '/cocreation/analysis', 'COCREATION_CTRL_Admin', 'analysis'));

COCREATION_CLASS_EventHandler::getInstance()->init();