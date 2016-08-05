<?php

namespace Araneum\Base\DoctrineFunctions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * DatePartFunction ::= "DATE_PART" "(" Identifier "," ArithmeticPrimary ")"
 */
class DatePart extends FunctionNode
{
    public $unit              = null;
    public $arithmeticPrimary = null;

    /**
     * Function Parse
     *
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $parser->match(Lexer::T_IDENTIFIER);
        /* @var $lexer Lexer */
        $lexer = $parser->getLexer();
        $this->unit = $lexer->token['value'];

        $parser->match(Lexer::T_COMMA);
        $this->arithmeticPrimary = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return "DATE_PART('".$this->unit."',".$this->arithmeticPrimary->dispatch($sqlWalker).")";
    }
}
