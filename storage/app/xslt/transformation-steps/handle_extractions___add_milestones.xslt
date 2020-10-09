<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="utf-8" indent="yes"/>

    <!--Jedes <extraction>-Element bekommt ein leeres milestone-Element mit der Textstelle (locus)
        als n-Attribut-->
    <xsl:template name="add_milestone">
        <xsl:for-each select=".">
          
            <xsl:element name="milestone">
                <xsl:attribute name="unit">textstelle</xsl:attribute>
                <xsl:attribute name="n">
                    <xsl:value-of select="./@locus"/>
                </xsl:attribute>
            </xsl:element>

          
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>
