# XMetaDissPlus Metadata Plugin

> The XMetaDissPlus Metadata plugin for [Open Monograph Press][omp] (OMP) has been developed at UB Heidelberg. It provides a filter to transform an OMP publication format into an [XMetaDissPlus][xmetadissplus] XML record. The [XMetaDissPlus 2.2.][xmetadissplus22] format has been defined by the [Deutsche Nationalbibliothek][dnb] (DNB).

## Requirements

* To generate a valid record, XMetaDissPlus requires the existence of a public identifier. You can either enable the OMP DOI plugin (`Management > Settings > Website > Plugins > Public Identifier Plugins > DOI`) that is included in OMP or install and enable the [URN DNB plugin][urn_dnb] for OMP.

## Installation

	git clone https://github.com/ub-heidelberg/xmdp22 /path/to/your/omp/plugins/metadata/
	php omp/tools/upgrade.php upgrade

## Bugs / Issues

You can report issues here: <https://github.com/ub-heidelberg/xmdp22/issues>

## License

This software is released under the the [GNU General Public License][gpl-licence].

See the [COPYING][gpl-licence] included with OMP for the terms of this license.

[pkp]: http://pkp.sfu.ca/
[xmetadissplus]: http://www.dnb.de/DE/Standardisierung/Metadaten/xMetadissPlus.html
[xmetadissplus22]: http://nbn-resolving.de/urn:nbn:de:101-2010052704
[urn_dnb]: https://github.com/ub-heidelberg/urn_dnb
[dnb]: http://www.dnb.de
[gpl-licence]: https://github.com/pkp/omp/blob/master/docs/COPYING

