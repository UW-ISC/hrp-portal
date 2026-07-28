<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Writer\Xls;

use WPDT\PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use WPDT\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\Wizard;
class ConditionalHelper
{
    /**
     * Formula parser.
     *
     * @var Parser
     */
    protected $parser;
    /**
     * @var mixed
     */
    protected $condition;
    /**
     * @var string
     */
    protected $cellRange;
    /**
     * @var null|string
     */
    protected $tokens;
    /**
     * @var int
     */
    protected $size;
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }
    /**
     * @param mixed $condition
     */
    public function processCondition($condition, string $cellRange) : void
    {
        $this->condition = $condition;
        $this->cellRange = $cellRange;
        if (\is_int($condition) || \is_float($condition)) {
            $this->size = $condition <= 65535 ? 3 : 0x0;
            $this->tokens = \pack('Cv', 0x1e, $condition);
        } else {
            try {
                $formula = Wizard\WizardAbstract::reverseAdjustCellRef((string) $condition, $cellRange);
                $this->parser->parse($formula);
                $this->tokens = $this->parser->toReversePolish();
                $this->size = \strlen($this->tokens ?? '');
            } catch (PhpSpreadsheetException $e) {
                // In the event of a parser error with a formula value, we set the expression to ptgInt + 0
                $this->tokens = \pack('Cv', 0x1e, 0);
                $this->size = 3;
            }
        }
    }
    public function tokens() : ?string
    {
        return $this->tokens;
    }
    public function size() : int
    {
        return $this->size;
    }
}
