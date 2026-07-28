<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Chart\Renderer;

/**
 * Jpgraph is not oficially maintained in Composer, so the version there
 * could be out of date. For that reason, all unit test requiring Jpgraph
 * are skipped. So, do not measure code coverage for this class till that
 * is fixed.
 *
 * This implementation uses abandoned package
 * https://packagist.org/packages/jpgraph/jpgraph
 *
 * @codeCoverageIgnore
 */
class JpGraph extends JpGraphRendererBase
{
    protected static function init() : void
    {
        static $loaded = \false;
        if ($loaded) {
            return;
        }
        // JpGraph is no longer included with distribution, but user may install it.
        // So Scrutinizer's complaint that it can't find it is reasonable, but unfixable.
        \WPDT\JpGraph\JpGraph::load();
        \WPDT\JpGraph\JpGraph::module('bar');
        \WPDT\JpGraph\JpGraph::module('contour');
        \WPDT\JpGraph\JpGraph::module('line');
        \WPDT\JpGraph\JpGraph::module('pie');
        \WPDT\JpGraph\JpGraph::module('pie3d');
        \WPDT\JpGraph\JpGraph::module('radar');
        \WPDT\JpGraph\JpGraph::module('regstat');
        \WPDT\JpGraph\JpGraph::module('scatter');
        \WPDT\JpGraph\JpGraph::module('stock');
        $loaded = \true;
    }
}
