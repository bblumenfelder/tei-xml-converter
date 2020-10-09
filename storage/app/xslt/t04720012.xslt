<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
    <xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="utf-8" indent="yes"/>



    <!--XSLT Identity Transform-->
    <xsl:template match="@* | node()">
        <xsl:copy>
            <xsl:apply-templates select="@* | node()"> </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>


    <!--ACTUAL Processing Instruction-->
    <xsl:template match="/">
      <p>
            <xsl:call-template name="handle_extractions">
                <xsl:with-param name="add-milestones">{true}</xsl:with-param>
            </xsl:call-template>
      </p>
    </xsl:template>

    <!-- IMPORTS -->
    
    <xsl:include href="transformation-steps/remove_given_milestones.xslt"/>
    <xsl:include href="transformation-steps/replace_l_with_lb.xslt"/>
    <xsl:include href="transformation-steps/remove_div_p_containers.xslt"/>
    <xsl:include href="transformation-steps/handle_extractions.xslt"/>
    <xsl:include href="transformation-steps/wrap_w_pc_seg.xslt"/>




</xsl:stylesheet>
