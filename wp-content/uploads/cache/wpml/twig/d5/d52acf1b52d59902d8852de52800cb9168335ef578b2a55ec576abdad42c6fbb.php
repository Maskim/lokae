<?php

namespace WPML\Core;

use \WPML\Core\Twig\Environment;
use \WPML\Core\Twig\Error\LoaderError;
use \WPML\Core\Twig\Error\RuntimeError;
use \WPML\Core\Twig\Markup;
use \WPML\Core\Twig\Sandbox\SecurityError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedTagError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFilterError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFunctionError;
use \WPML\Core\Twig\Source;
use \WPML\Core\Twig\Template;

/* troubleshooting.twig */
class __TwigTemplate_11131d76d64ba358ccd3731b9dfeb6e939e2b96ac26a87137ed43c499d31cb71 extends \WPML\Core\Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<div class=\"wrap wcml_trblsh\">
    <h2>";
        // line 2
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "troubl", []), "html", null, true);
        echo "</h2>
    <div class=\"wcml_trbl_warning\">
        <h3>";
        // line 4
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "backup", []), "html", null, true);
        echo "</h3>
    </div>
    <div class=\"trbl_variables_products\">
        <h3>";
        // line 7
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync", []), "html", null, true);
        echo "</h3>
        <ul>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_update_product_count\" />
                    ";
        // line 12
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "upd_prod_count", []), "html", null, true);
        echo "
                    <span class=\"var_status\">";
        // line 13
        echo \WPML\Core\twig_escape_filter($this->env, ($context["prod_with_variations"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 14
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "prod_var", []), "html", null, true);
        echo "</span>
                </label>
            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_product_variations\" checked=\"checked\" />
                    ";
        // line 20
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_var", []), "html", null, true);
        echo "
                    <span class=\"var_status\">";
        // line 21
        echo \WPML\Core\twig_escape_filter($this->env, ($context["prod_with_variations"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 22
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", []), "html", null, true);
        echo "</span>
                </label>

            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_gallery_images\" />
                    ";
        // line 29
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_gallery", []), "html", null, true);
        echo "
                    <span class=\"gallery_status\">";
        // line 30
        echo \WPML\Core\twig_escape_filter($this->env, ($context["prod_count"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 31
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", []), "html", null, true);
        echo "</span>
                </label>
            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_categories\" />
                    ";
        // line 37
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_cat", []), "html", null, true);
        echo "
                    <span class=\"cat_status\">";
        // line 38
        echo \WPML\Core\twig_escape_filter($this->env, ($context["prod_categories_count"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 39
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", []), "html", null, true);
        echo "</span>
                </label>

            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_duplicate_terms\" ";
        // line 45
        if (twig_test_empty(($context["all_products_taxonomies"] ?? null))) {
            echo "disabled=\"disabled\"";
        }
        echo " />
                    ";
        // line 46
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "dup_terms", []), "html", null, true);
        echo "
                    <select id=\"attr_to_duplicate\" ";
        // line 47
        if (twig_test_empty(($context["all_products_taxonomies"] ?? null))) {
            echo "disabled=\"disabled\"";
        }
        echo " >
                        ";
        // line 48
        if (twig_test_empty(($context["all_products_taxonomies"] ?? null))) {
            // line 49
            echo "                            <option value=\"0\" >";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "none", []), "html", null, true);
            echo "</option>
                        ";
        }
        // line 51
        echo "
                        ";
        // line 52
        $context["terms_count"] = 0;
        // line 53
        echo "                        ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["all_products_taxonomies"] ?? null));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["tax"]) {
            // line 54
            echo "                            ";
            if ($this->getAttribute($context["loop"], "first", [])) {
                // line 55
                echo "                                ";
                $context["terms_count"] = $this->getAttribute($context["tax"], "terms_count", []);
                // line 56
                echo "                            ";
            }
            // line 57
            echo "
                            <option value=\"";
            // line 58
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["tax"], "tax_key", []));
            echo "\" rel=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["tax"], "terms_count", []), "html", null, true);
            echo "\">
                                ";
            // line 59
            echo \WPML\Core\twig_escape_filter($this->env, \WPML\Core\twig_capitalize_string_filter($this->env, $this->getAttribute($this->getAttribute($context["tax"], "labels", []), "name", [])), "html", null, true);
            echo "
                            </option>
                        ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['tax'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 62
        echo "                    </select>
                    <span class=\"attr_status\">";
        // line 63
        echo \WPML\Core\twig_escape_filter($this->env, ($context["terms_count"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 64
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", []), "html", null, true);
        echo "</span>
                </label>

            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_stock\" />
                    ";
        // line 71
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_stock", []), "html", null, true);
        echo "
                    <span class=\"stock_status\">";
        // line 72
        echo \WPML\Core\twig_escape_filter($this->env, ($context["product_and_variations_count"] ?? null), "html", null, true);
        echo "</span>
                    <span>";
        // line 73
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", []), "html", null, true);
        echo "</span>
                </label>
            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_fix_relationships\" />
                    ";
        // line 79
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_relationships", []), "html", null, true);
        echo "
                    <span class=\"relationships_status\">";
        // line 80
        echo \WPML\Core\twig_escape_filter($this->env, ($context["fix_relationships_count"] ?? null), "html", null, true);
        echo "</span>
                    <span>";
        // line 81
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", []), "html", null, true);
        echo "</span>
                </label>
            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_deleted_meta\" />
                    ";
        // line 87
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_deleted_meta", []), "html", null, true);
        echo "
                    <span class=\"deleted_meta_status\">";
        // line 88
        echo \WPML\Core\twig_escape_filter($this->env, ($context["product_and_variations_count"] ?? null), "html", null, true);
        echo "</span>
                    <span>";
        // line 89
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", []), "html", null, true);
        echo "</span>
                </label>
            </li>
            <li>
                <button type=\"button\" class=\"button-secondary\" id=\"wcml_trbl\">";
        // line 93
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "start", []), "html", null, true);
        echo "</button>
                <input id=\"count_prod_variat\" type=\"hidden\" value=\"";
        // line 94
        echo \WPML\Core\twig_escape_filter($this->env, ($context["prod_with_variations"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_prod\" type=\"hidden\" value=\"";
        // line 95
        echo \WPML\Core\twig_escape_filter($this->env, ($context["prod_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_galleries\" type=\"hidden\" value=\"";
        // line 96
        echo \WPML\Core\twig_escape_filter($this->env, ($context["prod_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_categories\" type=\"hidden\" value=\"";
        // line 97
        echo \WPML\Core\twig_escape_filter($this->env, ($context["prod_categories_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_terms\" type=\"hidden\" value=\"";
        // line 98
        echo \WPML\Core\twig_escape_filter($this->env, ($context["terms_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_stock\" type=\"hidden\" value=\"";
        // line 99
        echo \WPML\Core\twig_escape_filter($this->env, ($context["product_and_variations_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_meta\" type=\"hidden\" value=\"";
        // line 100
        echo \WPML\Core\twig_escape_filter($this->env, ($context["product_and_variations_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_relationships\" type=\"hidden\" value=\"";
        // line 101
        echo \WPML\Core\twig_escape_filter($this->env, ($context["fix_relationships_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"sync_galerry_page\" type=\"hidden\" value=\"0\"/>
                <input id=\"sync_category_page\" type=\"hidden\" value=\"0\"/>
                <span class=\"spinner\"></span>
                ";
        // line 105
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_update_count", []);
        echo "
                ";
        // line 106
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_sync_variations", []);
        echo "
                ";
        // line 107
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_gallery_images", []);
        echo "
                ";
        // line 108
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_sync_categories", []);
        echo "
                ";
        // line 109
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_duplicate_terms", []);
        echo "
                ";
        // line 110
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_sync_stock", []);
        echo "
                ";
        // line 111
        echo $this->getAttribute(($context["nonces"] ?? null), "fix_relationships", []);
        echo "
                ";
        // line 112
        echo $this->getAttribute(($context["nonces"] ?? null), "sync_deleted_meta", []);
        echo "
            </li>
        </ul>
        ";
        // line 115
        if (($context["product_type_sync_needed"] ?? null)) {
            // line 116
            echo "            <h3>";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "delete_terms", []), "html", null, true);
            echo "</h3>
            <div>
                <button type=\"button\" class=\"button-secondary\" id=\"wcml_product_type_trbl\">";
            // line 118
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "start", []), "html", null, true);
            echo "</button>
                <span class=\"product_type_spinner\"></span>
                <span class=\"product_type_fix_done\" style=\"display: none\">";
            // line 120
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "product_type_fix_done", []), "html", null, true);
            echo "</span>
                ";
            // line 121
            echo $this->getAttribute(($context["nonces"] ?? null), "trbl_product_type_terms", []);
            echo "
            </div>
        ";
        }
        // line 124
        echo "    </div>
</div>";
    }

    public function getTemplateName()
    {
        return "troubleshooting.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  367 => 124,  361 => 121,  357 => 120,  352 => 118,  346 => 116,  344 => 115,  338 => 112,  334 => 111,  330 => 110,  326 => 109,  322 => 108,  318 => 107,  314 => 106,  310 => 105,  303 => 101,  299 => 100,  295 => 99,  291 => 98,  287 => 97,  283 => 96,  279 => 95,  275 => 94,  271 => 93,  264 => 89,  260 => 88,  256 => 87,  247 => 81,  243 => 80,  239 => 79,  230 => 73,  226 => 72,  222 => 71,  212 => 64,  208 => 63,  205 => 62,  188 => 59,  182 => 58,  179 => 57,  176 => 56,  173 => 55,  170 => 54,  152 => 53,  150 => 52,  147 => 51,  141 => 49,  139 => 48,  133 => 47,  129 => 46,  123 => 45,  114 => 39,  110 => 38,  106 => 37,  97 => 31,  93 => 30,  89 => 29,  79 => 22,  75 => 21,  71 => 20,  62 => 14,  58 => 13,  54 => 12,  46 => 7,  40 => 4,  35 => 2,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "troubleshooting.twig", "/var/www/lokae/wp-content/plugins/woocommerce-multilingual/templates/troubleshooting.twig");
    }
}
