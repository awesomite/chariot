<?php

namespace Awesomite\Chariot\Speedtest\Commands;

use Awesomite\Chariot\ParamDecorators\ContextInterface;
use Awesomite\Chariot\ParamDecorators\ParamDecoratorInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Speedtest\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class ProvidersCommands extends Command
{
    use XdebugTrait;
    use GlobalTimerTrait;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('test-providers')
            ->addOption('fast', 'f');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkXdebug();
        $this->startGlobalTimer();
        $this->warmUp();
        
        $number = $input->getOption('fast') ? 10000 : 500000;
        $timer1 = $this->handleWithProvider($number);
        $timer2 = $this->handleWithoutProvider($number);
        
        $output->writeln("<fg=black;bg=yellow>  Number of repetitions: {$number}  </>");
        $table = new Table($output);
        $table->setHeaders(['', 'With provider', 'Without provider', 'Diff [%]']);
        $table->addRow([
            'avg time [ms]',
            $this->formatTime($timer1->getTime() / $number),
            $this->formatTime($timer2->getTime() / $number),
            $this->formatDiff($timer1->getTime(), $timer2->getTime()),
        ]);
        
        $table->render();
        
        $output->writeln('');
        $this->printGlobalTimerFooter($output);
    }
    
    private function warmUp()
    {
        $this->handleWithProvider(1);
        $this->handleWithoutProvider(1);
    }
    
    private function formatTime(float $time)
    {
        return sprintf('% 10.4f', $time * 1000);
    }
    
    private function formatDiff(float $f1, float $f2)
    {
        return sprintf('% 8.2f', ($f1 - $f2)/$f2 * 100);
    }
    
    private function handleWithProvider(int $number): Timer
    {
        $router = PatternRouter::createDefault();
        $router->addParamDecorator(new class implements ParamDecoratorInterface {
            private $mapping = [
                1 => 'first',
                2 => 'second',
                3 => 'third',
            ];
            
            public function decorate(ContextInterface $context)
            {
                if ('showItem' !== $context->getHandler()) {
                    return;
                }
                
                $id = $context->getParams()['id'] ?? null;
                $title = $this->mapping[$id] ?? null;
                
                if (null !== $title) {
                    $context->setParam('title', $title);
                }
            }
        });
        $router->get('/items/{{ id :int }}-{{ title }}', 'showItem');
        
        $timer = new Timer();
        for ($i = 0; $i < $number; $i++) {
            $timer->start();
            $router->linkTo('showItem')->withParam('id', 3)->toString();
            $timer->stop();
        }
        
        return $timer;
    }

    private function handleWithoutProvider(int $number): Timer
    {
        $router = PatternRouter::createDefault();
        $router->get( '/items/{{ id :int }}-{{ title }}', 'showItem');

        $timer = new Timer();
        for ($i = 0; $i < $number; $i++) {
            $timer->start();
            $router->linkTo('showItem')->withParam('id', 3)->withParam('title', 'third')->toString();
            $timer->stop();
        }
        
        return $timer;
    }
}
