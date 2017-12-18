<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2017 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Eccube\Command;


use Eccube\Repository\PluginRepository;
use Eccube\Service\PluginService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PluginDisableCommand extends Command
{
    /**
     * @var PluginService
     */
    private $pluginService;

    /**
     * @var PluginRepository
     */
    private $pluginRepository;

    public function __construct(PluginService $pluginService, PluginRepository $pluginRepository)
    {
        parent::__construct();
        $this->pluginService = $pluginService;
        $this->pluginRepository = $pluginRepository;
    }

    protected function configure()
    {
        $this
            ->setName('eccube:plugin:disable')
            ->addOption('code', null, InputOption::VALUE_OPTIONAL, 'plugin code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $code = $input->getOption('code');

        if (empty($code)) {
            $io->error('code is required.');

            return;
        }

        $plugin = $this->pluginRepository->findByCode($code);
        if (is_null($plugin)) {
            $io->error("Plugin `$code` is not found.");
            return;
        }

        $this->pluginService->disable($plugin);

        $io->success('Plugin Disabled.');
    }
}