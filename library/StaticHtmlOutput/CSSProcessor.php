<?php


class CSSProcessor {

    public function __construct( $css_document, $wp_site_url ) {
        $this->wp_site_url = $wp_site_url;

        // parse CSS into easily modifiable form
        require_once dirname( __FILE__ ) . '/../CSSParser/Parser.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Settings.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Renderable.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/OutputFormat.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Comment/Comment.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Comment/Commentable.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Parsing/SourceException.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Parsing/OutputException.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Parsing/UnexpectedTokenException.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Property/AtRule.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Property/Charset.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Property/CSSNamespace.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Property/Import.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Property/Selector.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/RuleSet/RuleSet.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Rule/Rule.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/RuleSet/AtRuleSet.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/RuleSet/DeclarationBlock.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/Value.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/ValueList.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/RuleValueList.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/CSSFunction.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/CalcFunction.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/CalcRuleValueList.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/PrimitiveValue.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/Color.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/CSSString.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/LineName.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/Size.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/Value/URL.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/CSSList/CSSList.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/CSSList/CSSBlockList.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/CSSList/AtRuleBlockList.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/CSSList/Document.php';
        require_once dirname( __FILE__ ) . '/../CSSParser/CSSList/KeyFrame.php';

        $oCssParser = new Sabberworm\CSS\Parser( $css_document );
        $this->css_doc = $oCssParser->parse();
    }

    public function isInternalLink( $link ) {
        // check link is same host as $this->url and not a subdomain
        return parse_url( $link, PHP_URL_HOST ) === parse_url( $this->wp_site_url, PHP_URL_HOST );
    }

    public function normalizeURLs( $url ) {
        require_once dirname( __FILE__ ) . '/../URL2/URL2.php';
        $base = new Net_URL2( $url );

        foreach ( $this->css_doc->getAllValues() as $mValue ) {
            if ( $mValue instanceof Sabberworm\CSS\Value\URL ) {
                $original_link = $mValue->getURL();

                // TODO: benchmark trim vs str_replace
                // returned value contains surrounding quotes
                $original_link = trim( trim( $original_link, "'" ), '"' );

                if ( $this->isInternalLink( $original_link ) ) {
                    $absolute_url = new Sabberworm\CSS\Value\CSSString( $base->resolve( $original_link ) );
                    $mValue->setURL( $absolute_url );
                }
            }
        }
    }

    public function cleanup( $wp_site_environment, $overwrite_slug_targets ) {
        // PERF: ~ 30ms for HTML or CSS
        // TODO: skip binary file processing in func
        // TODO: move to CSSProcessor
        if ( $this->isCSS() ) {
            $regex = array(
                "`^([\t\s]+)`ism" => '',
                '`^\/\*(.+?)\*\/`ism' => '',
                "`([\n\A;]+)\/\*(.+?)\*\/`ism" => '$1',
                "`([\n\A;\s]+)//(.+?)[\n\r]`ism" => "$1\n",
                "`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism" => "\n",
            );

            $rewritten_CSS = preg_replace( array_keys( $regex ), $regex, $this->response['body'] );
            $this->setResponseBody( $rewritten_CSS );
        }
    }

    public function getCSS() {
        return $this->css_doc->render();
    }
}

