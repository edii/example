<?php

namespace Araneum\Base\DoctrineFunctions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

/**
 * Class Round
 *
 * @package Araneum\Base\DoctrineFunctions
 *
 * Round ::= "ROUND" "(" "ArithmeticPrimary" "," "ArithmeticExpression" ")"
 */
class Round extends FunctionNode
{
    /**
     * simpleArithmeticExpression
     *
     * @var    mixed
     * @access public
     */
    public $simpleArithmeticExpression;
    /**
     * roundPrecission
     *
     * @var    mixed
     * @access public
     */
    public $roundPrecission;

    /**
     * getSql
     *
     * @param  SqlWalker $sqlWalker
     * @access public
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'ROUND('.$sqlWalker->walkSimpleArithmeticExpression($this->simpleArithmeticExpression).','.$sqlWalker->walkStringPrimary($this->roundPrecission).')';
    }

    /**
     * parse
     *
     * @param  Parser $parser
     * @access public
     * @return void
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->simpleArithmeticExpression = $parser->SimpleArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->roundPrecission = $parser->ArithmeticExpression();
        if ($this->roundPrecission == null) {
            $this->roundPrecission = 0;
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
