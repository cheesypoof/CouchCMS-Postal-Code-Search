<?php require_once('couch/cms.php'); ?>
<?php require_once('postal-code.php'); ?>

<cms:template clonable='1' title='Postal Code Search'>
    <cms:editable label='Postal Code' name='postal_code' order='1' required='1' type='text' validator='regex=/^[0-9]{5}$/'/>
</cms:template>

<!doctype html>
<html>
<body>

<cms:form anchor='0' name='search' method='get'>
    <label for="pc">Postal Code:</label><br/>
    <cms:input name='pc' required='1' type='text' validator='regex=/^[0-9]{5}$/' value=''/><br/><br/>

    <label for="range">Range in Miles (0 for unlimited):</label><br/>
    <cms:input name='range' required='1' type='text' validator='regex=/^[0-9]{1,5}$/' value=''/><br/><br/>

    <input type="submit" value="Search"/>

    <cms:if k_error>
        <p style="color:red;">Please enter a valid Postal Code and Range.</p>
    </cms:if>

    <cms:if k_success>
        <cms:php>
            global $CTX;
            $location = new PostalCode($CTX->get('frm_pc'));
            $CTX->set('pc_query', $location->getSqlForPcsInRange(0, $CTX->get('frm_range')), 'global');
        </cms:php>

        <cms:if pc_query>
            <h3>Pages in Range:</h3>

            <cms:query limit='5' orderby='distance ASC' paginate='1' sql=pc_query>
                <cms:if k_paginated_top><ul></cms:if>

                <cms:pages id=page_id limit='1' masterpage=template_name>
                    <li>
                        <p><a href="<cms:show k_page_link/>"><cms:show k_page_title/></a><br/>
                        <cms:show pc/> is <cms:number_format distance decimal_precision='0'/> miles away</p>
                    </li>
                </cms:pages>

                <cms:if k_paginated_bottom></ul></cms:if>

                <cms:paginator/>
            </cms:query>
        <cms:else/>
            <p style="color:red;">Postal Code does not exist in database.</p>
        </cms:if>
    </cms:if>
</cms:form>

</body>
</html>

<?php COUCH::invoke(); ?>
