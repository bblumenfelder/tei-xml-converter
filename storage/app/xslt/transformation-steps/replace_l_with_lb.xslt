<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="utf-8" indent="yes"/>

    <!--Geschlossene <l>-Tags durch <lb> ersetzen und Text dahinter setzen-->
    <xsl:template match="//l">
        <xsl:element name="lb">
            <!-- n-Attribut mit JSON-Wert hinzufügen; 'original' ist die Verszahl des Verses,
                'hermeneus' zählt alle Verse im aktuellen Dokument -->
            <xsl:attribute name="n">{'original':'<xsl:value-of
                select="@n"/>','hermeneus':'<xsl:number level="any" count="//l"/>'}</xsl:attribute>
        </xsl:element>
        <xsl:apply-templates/>
    </xsl:template>

</xsl:stylesheet>
