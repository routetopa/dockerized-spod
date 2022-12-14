<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'mailbox');

$errors = array();

try
{
    $sql = "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_attachment` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `messageId` int(11) NOT NULL,
      `hash` varchar(13) NOT NULL,
      `fileName` varchar(255) NOT NULL,
      `fileSize` int(10) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `messageId` (`messageId`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

    Updater::getDbo()->query($sql);
}
catch ( Exception $ex )
{
    $errors[] = $ex;
}

try
{
    $sql = " CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "mailbox_file_upload` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `entityId` varchar(32) NOT NULL,
      `filePath` varchar(2048) NOT NULL,
      `fileName` varchar(255) NOT NULL,
      `fileSize` int(10) NOT NULL DEFAULT '0',
      `timestamp` int(10) NOT NULL DEFAULT '0',
      `userId` int(11) NOT NULL DEFAULT '0',
      `hash` varchar(32) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `hash` (`hash`,`userId`),
      KEY `entityId` (`entityId`),
      KEY `timestamp` (`timestamp`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";

    Updater::getDbo()->query($sql);

}
catch ( Exception $ex )
{
    $errors[] = $ex;
}

if ( !empty($errors) )
{
    printVar($errors);
}

