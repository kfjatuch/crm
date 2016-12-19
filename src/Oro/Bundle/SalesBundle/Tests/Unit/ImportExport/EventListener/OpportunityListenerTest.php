<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\ImportExport\EventListener;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CurrencyBundle\Provider\CurrencyProviderInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use Oro\Bundle\SalesBundle\ImportExport\EventListener\OpportunityListener;

class OpportunityListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnProcessAfter()
    {
        /** @var StrategyInterface $strategy */
        $strategy = $this->getMock('Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface');
        /** @var ContextInterface $context */
        $context      = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        $currencyConfigManager = $this
            ->getMockBuilder(CurrencyProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrencyList', 'getDefaultCurrency'])
            ->getMock();

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $organization = new Organization();
        $channel      = new Channel();
        $b2bCustomer  = new B2bCustomer();
        $entity       = new Opportunity();

        $b2bCustomerName = 'test_name';
        $b2bCustomer->setName($b2bCustomerName);
        $accountCustomer = AccountCustomerManager::createCustomer(new Account(), $b2bCustomer);
        $entity->setDataChannel($channel);
        $entity->setOrganization($organization);
        $entity->setCustomerAssociation($accountCustomer);

        $strategyEvent = new StrategyEvent($strategy, $entity, $context);
        $listener      = new OpportunityListener(
            new OpportunityRelationsBuilder(),
            $currencyConfigManager,
            $translator
        );
        $listener->onProcessAfter($strategyEvent);

        $this->assertSame($channel, $b2bCustomer->getDataChannel());
        $this->assertSame($organization, $b2bCustomer->getOrganization());
        $this->assertEquals($b2bCustomerName, $b2bCustomer->getAccount()->getName());
    }

    public function dataProviderOnProcessBefore()
    {
        return [
            'Record with empty budget amount and non-empty budget amount currency' => '',
            'Record with empty close revenue amount and non-empty close revenue amount currency' => '',
        ];
    }
}
