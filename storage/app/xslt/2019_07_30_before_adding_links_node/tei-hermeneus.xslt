<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:tei="http://www.tei-c.org/ns/1.0" exclude-result-prefixes="tei">
    <xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:variable name="empty" select="''"/>

    <!--XSLT Identity Transform-->
    <xsl:template match="@* | node()">
        <xsl:copy>
            <xsl:apply-templates select="@* | node()"/>
        </xsl:copy>
    </xsl:template>


    <!--Strip document of <TEI>-Tags-->
    <xsl:template match="/tei:TEI">
        <xsl:element name="hermeneus" namespace="http://www.tei-c.org/ns/1.0">
            <xsl:apply-templates select="@* | node()"/>
        </xsl:element>
    </xsl:template>


    <!-- Title-Info-->
    <xsl:template match="//tei:teiHeader">
        <div id="hermeneus-textinfo">
            <div class="hermeneus-textinfo__default">
                <div class="hermeneus-textinfo__default-author">
                    <xsl:value-of select="//tei:titleStmt/tei:author"/>
                </div>
                <div class="hermeneus-textinfo__default-author-short"></div>
                <div class="hermeneus-textinfo__default-title">
                    <xsl:value-of select="//tei:titleStmt/tei:title"/>
                </div>
                <div class="hermeneus-textinfo__default-locus">
                    <xsl:text> </xsl:text>
                </div>
            </div>
            <div class="hermeneus-textinfo__user">
                <div class="hermeneus-textinfo__user-author">
                    <xsl:text>bblumenfelder</xsl:text>
                </div>
                <div class="hermeneus-textinfo__user-title">
                    <xsl:value-of select="//tei:seriesStmt/tei:title"/>
                    
                </div>
                <div class="hermeneus-textinfo__user-subtitle">
                    <xsl:value-of select="//tei:seriesStmt/tei:title[@type='sub']"/>
                </div>
            </div>
        </div>
    </xsl:template>


    <!--Wrap text inside of <text>-Tags and apply namespace for child elements-->
    <xsl:template match="//tei:body">
        <xsl:element name="div" namespace="http://www.tei-c.org/ns/1.0">
            <xsl:attribute name="id">hermeneus-text</xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
        <!--APPEND COMMENTARY SECTION-->
        <xsl:element name="div" namespace="http://www.tei-c.org/ns/1.0">
            <xsl:attribute name="id">hermeneus-annotations</xsl:attribute>
            
            <!--<xsl:element name="spanGrp" namespace="http://www.tei-c.org/ns/1.0">
                <xsl:attribute name="type">commentary</xsl:attribute>
                <xsl:text> </xsl:text>
            </xsl:element>-->
            
            
        
        </xsl:element>
    </xsl:template>
    
   



    <!--Strip document of <p>-Tags-->
    <xsl:template match="//tei:p[not(@ana)]">
        <xsl:apply-templates/>
    </xsl:template>



    <!--Identity transform for <s> -->
    <xsl:template match="//tei:s">
        <xsl:copy>
            <xsl:apply-templates select="@* | node()"/>
        </xsl:copy>
    </xsl:template>


    <!--GAPS-->
    <!--Preserve as empty element-->
    <xsl:template match="//tei:gap">
        <xsl:copy>
            <!--Copies all attributes-->
            <xsl:copy-of select="@*" />
            <xsl:text> </xsl:text>
        </xsl:copy>
        <xsl:apply-templates/>
    </xsl:template>

    <!--SEGMENTS-->
    <xsl:template match="//tei:seg">
        <xsl:copy>
            <xsl:attribute name="class">
                <xsl:value-of select="@type"/>
            </xsl:attribute>
            <xsl:apply-templates select="@* | node()"/>
        </xsl:copy>
    </xsl:template>


    <!--LINEBREAKS-->
    <xsl:template match="//tei:lb">
        <xsl:element name="{local-name()}" namespace="http://www.tei-c.org/ns/1.0">

            <xsl:attribute name="class">linebreak</xsl:attribute>
            <xsl:attribute name="n">
                <xsl:value-of select="@n"/>
            </xsl:attribute>
            <xsl:text> </xsl:text>

        </xsl:element>
    </xsl:template>

    <!--WORDS-->
    <xsl:template name="words" match="//tei:w">

        <xsl:element name="{local-name()}" namespace="http://www.tei-c.org/ns/1.0">

            <!-- Word: bestimmung-attribute-->
            <xsl:attribute name="bestimmung">
                <xsl:value-of select="@lemma"/>
            </xsl:attribute>

            <!-- Include a narrow space BEFORE the word if preceding element is not pc[pre="true"]-->
            <xsl:if test="not(preceding-sibling::*[1][self::tei:pc[@pre = 'true']])">
                <xsl:text>&#8239;</xsl:text>
            </xsl:if>


            <!-- Word: textContent-->
            <xsl:value-of select="text()"/>


            <!-- IF the following sibling is not a punctuation mark, include a narrow space AFTER the word-->
            <xsl:if test="not(following-sibling::*[1][self::tei:pc])">
                <xsl:text>&#8239;</xsl:text>
            </xsl:if>


        </xsl:element>

    </xsl:template>






    <!-- TRIM WHITESPACE -->
    <!--Also removes whitespaces of empty elements like <milestone\> and <lb\>?-->
    <xsl:template match="text()">
        <xsl:apply-templates/>
    </xsl:template>






    <!--MILESTONES-->
    <!--Preserve as empty element-->
    <xsl:template match="//tei:milestone">
        <xsl:element name="milestone" namespace="http://www.tei-c.org/ns/1.0">
            <xsl:attribute name="unit">stanza</xsl:attribute>
            <xsl:text> </xsl:text>
        </xsl:element>
        <xsl:apply-templates/>
    </xsl:template>






    <!--PUNCTUATION MARKS-->
    <xsl:template match="//tei:pc">
        <xsl:element name="{local-name()}" namespace="http://www.tei-c.org/ns/1.0">

            <xsl:if test="self::tei:pc[@pre = 'true' or @pre = '1']">
                <xsl:text>&#8239;</xsl:text>
            </xsl:if>

            <xsl:value-of select="."/>
            <xsl:if
                test="
                    (not(following-sibling::*[1][self::tei:pc]) or following-sibling::*[1][self::tei:pc[@type = 'quotation']])
                    and not(self::tei:pc[@pre = 'true'])
                    and not(following-sibling::*[1][self::tei:pc[@pre = 'false' or @pre = '0']])">
                <xsl:text>&#8239;</xsl:text>
            </xsl:if>
        </xsl:element>
    </xsl:template>



</xsl:stylesheet>
