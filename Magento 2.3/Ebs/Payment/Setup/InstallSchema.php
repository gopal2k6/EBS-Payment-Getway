<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ebs\Payment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

		/**
			$installer->run("
					CREATE TABLE `{$this->getTable('secureebs_api_debug')}` (
					  `debug_id` int(10) unsigned NOT NULL auto_increment,
					  `debug_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
					  `request_body` text,
					  `response_body` text,
					  PRIMARY KEY  (`debug_id`),
					  KEY `debug_at` (`debug_at`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");
		*/
		
		
        /**
         * Create table 'secureebs_api_debug'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('secureebs_api_debug'))
            ->addColumn(
                'debug_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'DEBUG ID'
            )
            ->addColumn(
                'debug_at',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'DEBUG AT'
            )
            ->addColumn(
                'request_body',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => false, 'default' => 'simple'],
                'REQUEST BODY'
            )
            ->addColumn(
                'response_body',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                [],
                'RESPONSE BODY'
            )
            ->addIndex(
                $installer->getIdxName('debug_at', ['debug_at']),
                ['debug_at']
            )
            ->setComment('Catalog Product Table');

			$installer->getConnection()->createTable($table);

			$installer->endSetup();

    }
}
