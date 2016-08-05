<?php

namespace Araneum\Base\DoctrineFunctions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

/**
 * CastFunction ::= "CAST" "(" StringPrimary "AS" Identifier ")"
 */
class Cast extends FunctionNode
{
    public $convertExpression = null;
    public $unit              = null;

    /**
     * Function parse
     *
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->convertExpression = $parser->StringPrimary();

        $parser->match(Lexer::T_AS);

        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $this->unit = $lexer->token['value'];

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('CAST(%s AS %s)', $this->convertExpression->dispatch($sqlWalker), $this->unit);
    }
}
