<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="utf-8" indent="yes"/>
    
    <!--DIVs und Ps entfernen-->
    <xsl:template match="//reg">
        <xsl:apply-templates/>
    </xsl:template>
    
</xsl:stylesheet>