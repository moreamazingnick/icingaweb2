<?php

namespace Icinga\Web\Navigation;

use Icinga\Application\Hook\HealthHook;
use Icinga\Authentication\Auth;
use ipl\Html\Attribute;
use ipl\Html\Attributes;
use ipl\Html\BaseHtmlElement;
use ipl\Html\HtmlElement;
use ipl\Html\HtmlString;
use ipl\Html\Text;
use ipl\Web\Url;
use ipl\Web\Widget\Icon;
use ipl\Web\Widget\StateBadge;

class ConfigMenu extends BaseHtmlElement
{
    const STATE_OK = 'ok';
    const STATE_CRITICAL = 'critical';
    const STATE_WARNING = 'warning';
    const STATE_PENDING = 'pending';
    const STATE_UNKNOWN = 'unknown';

    protected $defaultAttributes = [
        'class' => 'nav nav-level-1'
    ];

    protected $tag = 'ul';

    protected $children = [
        'system' => [
            'title' => 'System',
            'items' => [
                'about' => [
                    'label' => 'About',
                    'url' => 'about'
                ],
                'health' => [
                    'label' => 'Health',
                    'url' => 'health',
                ],
                'announcements' => [
                    'label' => 'Announcements',
                    'url' => 'announcements'
                ],
                'sessions' => [
                    'label' => 'User Sessions',
                    'permission'  => 'application/sessions',
                    'url'         => 'manage-user-devices'
                ]
            ]
        ],
        'configuration' => [
            'title' => 'Configuration',
            'items' => [
                'application' => [
                    'label' => 'Application',
                    'url' => 'config'
                ],
                'authentication' => [
                    'label' => 'Access Control',
                    'permission'  => 'config/access-control/*',
                    'url' => 'role'
                ],
                'navigation' => [
                    'label' => 'Shared Navigation',
                    'permission'  => 'config/navigation',
                    'url' => 'navigation'
                ],
                'modules' => [
                    'label' => 'Modules',
                    'permission'  => 'config/modules',
                    'url' => 'config/modules'
                ]
            ]
        ],
        'logout' => [
            'items' => [
                'logout' => [
                    'label'       => 'Logout',
                    'atts'  => [
                        'target' => '_self',
                        'class' => 'nav-item-logout'
                    ],
                    'url'         => 'authentication/logout'
                ]
            ]
        ]
    ];

    public function getHealthCount()
    {
        $count = 0;
        $title = null;
        $worstState = null;
        foreach (HealthHook::collectHealthData()->select() as $result) {
            if ($worstState === null || $result->state > $worstState) {
                $worstState = $result->state;
                $title = $result->message;
                $count = 1;
            } elseif ($worstState === $result->state) {
                $count++;
            }
        }

        switch ($worstState) {
            case HealthHook::STATE_OK:
                $count = 0;
                break;
            case HealthHook::STATE_WARNING:
                $this->state = self::STATE_WARNING;
                break;
            case HealthHook::STATE_CRITICAL:
                $this->state = self::STATE_CRITICAL;
                break;
            case HealthHook::STATE_UNKNOWN:
                $this->state = self::STATE_UNKNOWN;
                break;
        }

        $this->title = $title;

        return $count;
    }

    protected function createHealthBadge()
    {
        return $this->getHealthCount() == 0 ? '' : new StateBadge($this->getHealthCount(), 'critical');
    }

    protected function createLevel2Menu()
    {
        $level2Nav = HtmlElement::create(
            'div',
            Attributes::create(['class' => 'nav-level-2 flyout caret-bottom'])
        );

        $this->assembleLevel2Nav($level2Nav);

        return $level2Nav;
    }

    protected function assembleLevel2Nav(BaseHtmlElement $level2Nav)
    {
        foreach ($this->children as $key => $c) {
            if ($key == 'health') {
                $healthBadge = $this->createHealthBadge();
            }

            if (isset($c['title'])) {
                $level2Nav->add(HtmlElement::create(
                    'h3',
                    [],
                    t($c['title'])
                ));
            }

            $ul = HtmlElement::create('ul', ['class' => 'nav']);
            foreach ($c['items'] as $item) {
                $li = HtmlElement::create(
                    'li',
                    isset($item['atts']) ? $item['atts'] : [],
                    [
                        HtmlElement::create(
                            'a',
                            Attributes::create(['href' => $item['url']]),
                            t($item['label'])
                        ),
                        isset($healthBadge) ? $healthBadge : ''
                    ]
                );
                $li->addAttributes(['class' => 'nav-item']);

                $ul->add($li);
            }
            $level2Nav->add($ul);
        }
    }

    protected function addItem()
    {
    }

    protected function addSection($title)
    {
    }

    protected function getSection($section)
    {
    }

    protected function assemble()
    {
        $username = Auth::getInstance()->getUser()->getUsername();

        $this->add(
            HtmlElement::create(
                'li',
                ['class' => 'nav-item segmented-nav-item'],
                [
                    HtmlElement::create(
                        'a',
                        Attributes::create(['href' => Url::fromPath('account')]),
                        [
                            HtmlElement::create(
                                'i',
                                Attributes::create(['class' => 'user-ball']),
                                Text::create($username[0])
                            ),
                            $username
                        ]
                    ),
                    HtmlElement::create(
                        'span',
                        Attributes::create(['class' => 'hover-me']),
                        [
                            new Icon('cog'),
                            $this->createHealthBadge(),
                        ]
                    ),
                    $this->createLevel2Menu()
                ]
            )
        );
    }
}
