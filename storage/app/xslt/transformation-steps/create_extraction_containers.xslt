<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="utf-8" indent="yes"/>
    
    <!--Alle <p> und <div>, die Text enthalten werden durch <extraction> ersetzt-->
    <xsl:template match="//p[text()[normalize-space()]] | //div[text()[normalize-space()]]">
        <extraction>
           
            <xsl:apply-templates select="@* | node()"/>
         
        </extraction>
    </xsl:template>
    
</xsl:stylesheet>