<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="utf-8" indent="yes"/>


    <xsl:include href="handle_extractions___add_milestones.xslt"/>


    <xsl:template name="handle_extractions">
        <xsl:param name="add-milestones"/>
        <xsl:for-each select="//extraction">

            <xsl:choose>
                <!--Alle <extraction>-Elemente, die das Attribute keep="yes" haben, werden durch divs als
                textpart ersetzt-->
                <xsl:when test="self::*[@keep = 'yes']">
                    <xsl:element name="seg">
                        <xsl:attribute name="n">
                            <xsl:value-of select="./@locus"/>
                        </xsl:attribute>
                        <xsl:attribute name="type">textpart</xsl:attribute>
                        <xsl:attribute name="subtype">user-selection</xsl:attribute>
                        <xsl:if test="$add-milestones = '{true}'">
                            <xsl:call-template name="add_milestone"/>
                        </xsl:if>

                        <xsl:apply-templates/>
                    </xsl:element>
                </xsl:when>
                <!--Im anderen Fall passiert nichts-->
                <xsl:otherwise>
                    <xsl:if test="$add-milestones = '{true}'">
                        <xsl:call-template name="add_milestone"/>
                    </xsl:if>

                    <xsl:apply-templates/>
                </xsl:otherwise>
            </xsl:choose>

        </xsl:for-each>



    </xsl:template>

</xsl:stylesheet>
