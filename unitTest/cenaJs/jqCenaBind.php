<!DOCTYPE html>
<html lange="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="../../common/cena.css" rel="stylesheet" type="text/css">
    <!-- InstanceBeginEditable name="doctitle" -->
    <title>Unit Tests ::Cena-DTA Development</title>
    <script src="../../common/jquery-1.6.2.js"></script>
    <link rel="stylesheet" href="../../common/qunit.css" type="text/css" media="screen" />
    <script type="text/javascript" src="../../common/qunit.js"></script>
    <script type="text/javascript" src="../../cenaJs/jqCena.js"></script>

    <script>
      $(document).ready(function(){
        
        var cena_env1 = [
          {
            "envelope":"envelope",
            "scheme":"Cena",
            "model":"dao_contact100",
            "type":"get",
            "id":"1",
            "cena_id":"Cena.dao_contact100.get.1",
            "prop":{
              "contact_id":"1",
              "contact_name":"name#0",
              "contact_gender":"1",
              "contact_type":"1",
              "contact_date":"2011-09-10"
            },
            "link":{},
            "num_elem":4
          },
          {
            "envelope":"envelope",
            "scheme":"Cena",
            "model":"dao_contact110",
            "type":"get",
            "id":"1",
            "cena_id":"Cena.dao_contact110.get.1",
            "prop":{
              "connect_id":"1",
              "connect_info":"CenaDTA",
              "connect_type":"1",
              "contact_id":"12"
            },
            "link":{
              "contact_id":"Cena.dao_contact100.get.12"
            },
            "num_elem":4
          }
        ];
        
        module("Module Qunit");

        test("a basic test example", function() {
          ok( true, "this test is fine" );
          var value = "hello";
          equal( value, "hello", "We expect value to be hello" );
        });

        module("Module jqCena");

        test("jqCena message", function() {
          ok( true, "all pass" );
          
          // test message
          
          var cena_msg = "#cena_msg"; // ToDo: get from cenaInitOptions
          var message  = "first cena message";
          $( cena_msg ).empty();
          
          $().cena( "message", message );
          var msg_get = $( cena_msg ).text();
          ok( msg_get, "message not displayed. ");
          equal( message, msg_get, "messages different. ");
          
        });

        test("jqCena bind", function() {
          
          // bind test1: simple bind with input:text
          
          $() .cena( { 	                   // initialize cena.
            env_src:   '#cena_test1_src',  // source envelop id.
            env_post:  '#cena_test1_post', // id to post bound envelope. 
            bind_type: 'replace'           // show contents.
          })
          .cena( 'add', cena_env1 )
          .cena( 'bind',  {                // bind env_data to envelope.
            model: 'dao_contact100'        // bind only this model.
          })
          .cena( 'clean' )
          .cena( 'activate' );
          
          var found1 = $( "#cena_test1_post" ).text();
          $().cena( "message", found1 );
          ok( found1.trim(), "dao_contact100's contact_name not bound" );
          equal( found1.trim(), cena_env1[0].prop.contact_name, "contact name is different." );
          
          
          // bind test2: simple bind with input:text
          
          $() .cena( { 	                   // initialize cena.
            env_src:   '#cena_test2_src',  // source envelop id.
            env_post:  '#cena_test2_post', // id to post bound envelope. 
            bind_type: 'replace'           // show contents.
          })
          .cena( 'add', cena_env1 )
          .cena( 'bind',  {                // bind env_data to envelope.
            model: 'dao_contact110'        // bind only this model.
          })
          .cena( 'clean' )
          .cena( 'activate' );
          
          var found2 = $( "#cena_test2_post" ).text();
          ok( found2.trim(), "dao_contact110's connect_info not bound" );
          equal( found2.trim(), cena_env1[1].prop.connect_info, "contact name is different." );
          
          
          // bind test3: simple bind with input:radio
          
          $() .cena( { 	                   // initialize cena.
            env_src:   '#cena_test3_src',  // source envelop id.
            env_post:  '#cena_test3_post', // id to post bound envelope. 
            bind_type: 'replace'           // show contents.
          })
          .cena( 'add', cena_env1 )
          .cena( 'bind',  {                // bind env_data to envelope.
            model: 'dao_contact100'        // bind only this model.
          })
          .cena( 'clean' )
          .cena( 'activate' );
          
          $( "#cena_test3_src" ).remove(); 
          var found3 = $( "#cena_test3_post" ).text();
          $().cena( "message", found3 );
          ok( found3.trim(), "dao_contact100's contact_gender not bound with radio" );
          equal( found3.trim(), "male_test3", "contact gender is different." );
          
          
          // bind test4: simple bind with select
          
          $() .cena( { 	                   // initialize cena.
            env_src:   '#cena_test4_src',  // source envelop id.
            env_post:  '#cena_test4_post', // id to post bound envelope. 
            bind_type: 'replace'           // show contents.
          })
          .cena( 'add', cena_env1 )
          .cena( 'bind',  {                // bind env_data to envelope.
            model: 'dao_contact100'        // bind only this model.
          })
          .cena( 'clean' )
          .cena( 'activate' );
          
          $( "#cena_test4_src" ).remove(); 
          var found4 = $( "#cena_test4_post" ).text();
          $().cena( "message", found4 );
          ok( found4.trim(), "dao_contact100's contact_type not bound with select" );
          equal( found4.trim(), "friend_test4", "contact gender is different." );
          
          
          // bind test5: simple bind with span
          
          $() .cena( { 	                   // initialize cena.
            env_src:   '#cena_test5_src',  // source envelop id.
            env_post:  '#cena_test5_post', // id to post bound envelope. 
            bind_type: 'replace'           // show contents.
          })
          .cena( 'add', cena_env1 )
          .cena( 'bind',  {                // bind env_data to envelope.
            model: 'dao_contact100'        // bind only this model.
          })
          .cena( 'clean' )
          .cena( 'activate' );
          
          $( "#cena_test5_src" ).remove(); 
          var found5 = $( "#cena_test5_post" ).text();
          $().cena( "message", found5 );
          ok( found5.trim(), "dao_contact100's cena_id not bound with span" );
          equal( found5.trim(), cena_env1[0].prop.contact_name, "cena_id is different." );
          
        });

      });
    </script>
    <!-- InstanceEndEditable -->

  </head>
  <body>
    <div id="header">
      <p><a href="../../index.php" class="headerTitle"><strong>Cena</strong> DTA Developments </a></p>
      <div class="menus">|&nbsp;<a href="../../index.php">Top</a>&nbsp;|&nbsp;<a href="http://wsjp.blogspot.com/" target="_blank"></a></div>
      <p align="left" class="headDesc">for HTML5 applications using local databases.</p>
    </div>
    <div id="contents">
      <!-- InstanceBeginEditable name="contents" -->
      <h1><span class="bread"><a href="../../index.php">Cena-DTA</a>:: Unit Test:: cenaJs</span>Unit Test for cenaJs</h1>
      <h1 id="qunit-header">QUnit example</h1>
      <h2 id="qunit-banner"></h2>
      <div id="qunit-testrunner-toolbar"></div>
      <h2 id="qunit-userAgent"></h2>
      <ol id="qunit-tests"></ol>
      <div id="qunit-fixture">test markup, will be hidden</div>

      <!-- DOMs for cenaJs test  -->

      <h2>test message</h2>
      <div id="cena_msg"></div>

      <!-- text1: bind with text:input -->
      <h2>bind test1</h2>
      <ul id="cena_test1_post">
        <li id="cena_test1_src">
          <input type="text" size="25" name="contact_name" class="cena_element" value="" />
        </li>
      </ul>
      <!-- text2: bind with text:input -->
      <h2>bind test2</h2>
      <ul id="cena_test2_post">
        <li id="cena_test2_src">
          <input type="text" size="25" name="connect_info" class="cena_element" value="" />
        </li>
      </ul>
      <!-- text3: bind with text:radio -->
      <h2>bind test3</h2>
      <ul id="cena_test3_post">
        <li id="cena_test3_src" style="display:none;">
          <label>
            <input type="radio" name="contact_gender" class="cena_element" value="1">
            male_test3
          </label>
          <label>
            <input type="radio" name="contact_gender" class="cena_element" value="2">
            female_test3
          </label>
        </li>
      </ul>
      <!-- text4: bind with text:radio -->
      <h2>bind test4</h2>
      <ul id="cena_test4_post">
        <li id="cena_test4_src" style="display:none;">
          <select name="contact_type" class="cena_element">
            <option value="1">friend_test4</option>
            <option value="2">work_test4</option>
            <option value="3">family_test4</option>
            <option value="4">other_test4</option>
          </select>
        </li>
      </ul>
      <!-- text5: bind with span -->
      <h2>bind test5</h2>
      <ul id="cena_test5_post">
        <li id="cena_test5_src" style="display:none;">
          <span class="cena_element" name="contact_name"></span>
        </li>
      </ul>

      <!-- end of test DOMs -->
    </div>
    <!-- InstanceEndEditable -->
  </div>
  <div id="footer">
    <table  border="0" align="center" cellpadding="0" cellspacing="0">
      <tr valign="top">
        <td valign="bottom" nowrap class="footDesc"><p>Cena developed 
            by <a href="../../../index.php"><strong>WorkSpot.JP</strong></a>&nbsp;
          </p>      </td>
        <td width="6">&nbsp;</td>
        <td width="100"><a href="../../../serv/index.php"><img src="../../../com/img/bar_ser.gif" width="100" height="30" border="0" alt="Service（業務内容） （写真：デビルズタワー国定公園、アメリカ）"></a></td>
        <td width="100"><a href="../../../expc/index.php"><img src="../../../com/img/bar_exp.gif" width="100" height="30" border="0" alt="Experience（実績･経験）　（写真：紀伊半島にある筆薮滝）"></a></td>
        <td width="100"><a href="../../../prof/index.php"><img src="../../../com/img/bar_pro.gif" width="100" height="30" border="0" alt="Profile（経歴）　（写真：バッドランド国立公園、アメリカ）"></a></td>
        <td width="100"><a href="../../../tech/index.php"><img src="../../../com/img/bar_tec.gif" width="100" height="30" border="0" alt="Technology（技術）　（写真：東京フォーラム）"></a></td>
      </tr>
      <tr valign="top">
        <td colspan="6" align="center" nowrap><span class="copyright">copyright (c) 2010-<?php echo date('Y'); ?> WorkSpot.JP</span></td>
      </tr>
    </table>
  </div>
</body>
</html>