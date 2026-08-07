<?php

declare (strict_types=1);
namespace WPDT\Melograno\UsageTracker\Core;

class NoticeManager
{
    private string $optionName;
    public function __construct(string $optionName)
    {
        $this->optionName = $optionName;
    }
    public function isArmed() : bool
    {
        return \get_option($this->optionName) === 'yes';
    }
    public function arm() : void
    {
        \update_option($this->optionName, 'yes', \true);
    }
    public function dismiss() : void
    {
        \update_option($this->optionName, 'no', \true);
    }
    public function delete() : void
    {
        \delete_option($this->optionName);
    }
}
