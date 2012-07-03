package maarchcm;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Hashtable;
import java.util.Iterator;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JApplet;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import org.w3c.dom.Document;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;
import netscape.javascript.JSObject;

/**
 *
 * @author Laurent Giovannoni
 */
public class MaarchCM extends JApplet {
    //INIT PARAMETERS
    protected String url;
    protected String objectType;
    protected String objectTable;
    protected String objectId;
    protected String userLocalDirTmp;
    
    protected String messageStatus;
    
    Hashtable app = new Hashtable();
    Hashtable messageResult = new Hashtable();
    
    //XML PARAMETERS
    protected String status;
    protected String appPath;
    protected String fileContent;
    protected String fileExtension;
    protected String error;
    protected String endMessage;
    protected String os;
    
    protected String fileContentTosend;
    
    public myLogger logger;
    
    public void init()
    {
        System.out.println("----------BEGIN PARAMETERS----------");
        this.url = this.getParameter("url");
        this.objectType = this.getParameter("objectType");
        this.objectTable = this.getParameter("objectTable");
        this.objectId = this.getParameter("objectId");
        this.userLocalDirTmp = this.getParameter("userLocalDirTmp");
        System.out.println("URL : " + this.url);
        System.out.println("OBJECT TYPE : " + this.objectType);
        System.out.println("OBJECT TABLE : " + this.objectTable);
        System.out.println("OBJECT ID : " + this.objectId);
        System.out.println("USER LOCAL DIR TMP : " + this.userLocalDirTmp);
        System.out.println("----------END PARAMETERS----------");
        try {
            this.editObject();
            this.destroy();
            this.stop();
            System.exit(0);
        } catch (Exception ex) {
            Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
    public void test(String[] args)
    {
        System.out.println("----------TESTS----------");
        System.out.println("----------BEGIN PARAMETERS----------");
        this.url = args[0];
        this.objectType = args[1];
        this.objectTable = args[2];
        this.objectId = args[3];
        this.userLocalDirTmp = args[4];
        System.out.println("URL : " + this.url);
        System.out.println("OBJECT TYPE : " + this.objectType);
        System.out.println("OBJECT TABLE : " + this.objectTable);
        System.out.println("OBJECT ID : " + this.objectId);
        System.out.println("USER LOCAL DIR TMP : " + this.userLocalDirTmp);
        System.out.println("----------END PARAMETERS----------");
        try {
            this.editObject();
        } catch (Exception ex) {
            Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
    public void parse_xml(InputStream flux_xml) throws SAXException, IOException, ParserConfigurationException
    {
        this.logger.log("----------BEGIN PARSE XML----------", Level.INFO);
        DocumentBuilder builder = DocumentBuilderFactory.newInstance().newDocumentBuilder();
        Document doc = builder.parse(flux_xml);
        this.messageResult.clear();
        NodeList level_one_list = doc.getChildNodes();
        for (Integer i=0; i < level_one_list.getLength(); i++) {
            NodeList level_two_list = level_one_list.item(i).getChildNodes();
            if ("SUCCESS".equals(level_one_list.item(i).getNodeName())) {
                for(Integer j=0; j < level_one_list.item(i).getChildNodes().getLength(); j++ ) {
                    this.messageResult.put(level_two_list.item(j).getNodeName(),level_two_list.item(j).getTextContent());
                }
                this.messageStatus = "SUCCESS";
            } else if ("ERROR".equals(level_one_list.item(i).getNodeName()) ) {
                for(Integer j=0; j < level_one_list.item(i).getChildNodes().getLength(); j++ ) {
                    this.messageResult.put(level_two_list.item(j).getNodeName(),level_two_list.item(j).getTextContent());
                }
                this.messageStatus = "ERROR";
            }
        }
        this.logger.log("----------END PARSE XML----------", Level.INFO);
    }
    
    public void processReturn(Hashtable result) {
        Iterator itValue = result.values().iterator(); 
        Iterator itKey = result.keySet().iterator();
        while(itValue.hasNext()){
            String value = (String)itValue.next();
            String key = (String)itKey.next();
            this.logger.log(key + " : " + value, Level.INFO);
            if ("STATUS".equals(key)) {
                this.status = value;
            }
            if ("OBJECT_TYPE".equals(key)) {
                this.objectType = value;
            }
            if ("OBJECT_TABLE".equals(key)) {
                this.objectTable = value;
            }
            if ("OBJECT_ID".equals(key)) {
                this.objectId = value;
            }
            if ("APP_PATH".equals(key)) {
                //this.appPath = value;
            }
            if ("FILE_CONTENT".equals(key)) {
                this.fileContent = value;
            }
            if ("FILE_EXTENSION".equals(key)) {
                this.fileExtension = value;
            }
            if ("ERROR".equals(key)) {
                this.error = value;
            }
            if ("END_MESSAGE".equals(key)) {
                this.endMessage = value;
            }
        }
        //send message error to Maarch if necessary
        if (!this.error.isEmpty()) {
            this.sendJsMessage(this.error.toString());
        }
    }
    
    /**
     * @param args the command line arguments
     */
    public static void main(String[] args) {
        try{
            System.out.println(args[0]);
            System.out.println(args[1]);
            System.out.println(args[2]);
            System.out.println(args[3]);
            System.out.println(args[4]);
            MaarchCM maarchCM = new MaarchCM();
            maarchCM.test(args);
        }
       catch(Exception e) {
           String exMessage = e.toString();
           System.out.println(exMessage);
       }
    }
    
    public String editObject() throws Exception {
        System.out.println("----------BEGIN EDIT OBJECT----------");
        System.out.println("----------BEGIN LOCAL DIR TMP IF NOT EXISTS----------");
        String os = System.getProperty("os.name").toLowerCase();
        boolean isUnix = os.indexOf("nix") >= 0 || os.indexOf("nux") >= 0;
        boolean isWindows = os.indexOf("win") >= 0;
        boolean isMac = os.indexOf("mac") >= 0;
        //this.userLocalDirTmp = System.getProperty("user.home");
        this.userLocalDirTmp = "c:\\maarch";
        fileManager fM = new fileManager();
        fM.createUserLocalDirTmp(this.userLocalDirTmp);
        if (isWindows) {
            System.out.println("This is Windows");
            this.userLocalDirTmp = this.userLocalDirTmp + "\\maarchTmp\\";
            this.appPath = this.userLocalDirTmp + "start.bat";
            this.os = "win";
        } else if (isMac) {
            System.out.println("This is Mac");
            this.userLocalDirTmp = this.userLocalDirTmp + "/maarchTmp/";
            this.appPath = this.userLocalDirTmp + "start.sh";
            this.os = "mac";
        } else if (isUnix) {
            System.out.println("This is Unix or Linux");
            this.userLocalDirTmp = this.userLocalDirTmp + "/maarchTmp/";
            this.appPath = this.userLocalDirTmp + "start.sh";
            this.os = "linux";
        } else {
            System.out.println("Your OS is not supported!!");
        }
        System.out.println("APP PATH: " + this.appPath);
        System.out.println("----------BEGIN LOCAL DIR TMP IF NOT EXISTS----------");
        
        fM.createUserLocalDirTmp(this.userLocalDirTmp);
        System.out.println("----------END LOCAL DIR TMP IF NOT EXISTS----------");
        
        System.out.println("Create the logger");
        this.logger = new myLogger(this.userLocalDirTmp);
        this.logger.log("----------BEGIN OPEN REQUEST----------", Level.INFO);
        String urlToSend = this.url + "?action=editObject&objectType=" + this.objectType 
                        + "&objectTable=" + this.objectTable + "&objectId=" + this.objectId;
        sendHttpRequest(urlToSend, "none");
        this.logger.log("MESSAGE STATUS : " + this.messageStatus.toString(), Level.INFO);
        this.logger.log("MESSAGE RESULT : ", Level.INFO);
        this.processReturn(this.messageResult);
        this.logger.log("----------END OPEN REQUEST----------", Level.INFO);
        
        String fileToEdit = "thefile." + this.fileExtension;
        //if ("start".equals(this.appPath)) {
            this.logger.log("----------BEGIN CREATE THE BAT TO LAUNCH IF NECESSARY----------", Level.INFO);
            this.logger.log("create the file : "  + this.appPath, Level.INFO);
            fM.createBatFile(this.appPath, this.userLocalDirTmp, fileToEdit, this.os);
            this.logger.log("----------END CREATE THE BAT TO LAUNCH IF NECESSARY----------", Level.INFO);
        //}
        
        if ("ok".equals(this.status)) {
            this.logger.log("RESPONSE OK", Level.INFO);
            
            this.logger.log("----------BEGIN EXECUTION OF THE EDITOR----------", Level.INFO);
            this.logger.log("CREATE FILE IN LOCAL PATH", Level.INFO);
            fM.createFile(this.fileContent, this.userLocalDirTmp + fileToEdit);
            //final String exec = this.appPath + " " + this.userLocalDirTmp + fileToEdit;
            final String exec = this.appPath;
            this.logger.log("EXEC PATH : " + exec, Level.INFO);
            Process proc = fM.launchApp(exec);
            proc.waitFor();
            int ev = 0;
            if (proc.waitFor() != 0) {
                ev = proc.exitValue();
            }
            this.logger.log("RESULT OF THE EXECUTION" + ev, Level.INFO);
            this.logger.log("----------END EXECUTION OF THE EDITOR----------", Level.INFO);
            
            this.logger.log("----------BEGIN RETRIEVE CONTENT OF THE OBJECT----------", Level.INFO);
            this.fileContentTosend = fM.encodeFile(this.userLocalDirTmp + "thefile." + this.fileExtension);
            this.logger.log("----------END RETRIEVE CONTENT OF THE OBJECT----------", Level.INFO);
            
            this.logger.log("----------BEGIN SEND OF THE OBJECT----------", Level.INFO);
            String urlToSave = this.url + "?action=saveObject&objectType=" + this.objectType 
                            + "&objectTable=" + this.objectTable + "&objectId=" + this.objectId;
            sendHttpRequest(urlToSave, this.fileContentTosend);
            this.logger.log("MESSAGE STATUS : " + this.messageStatus.toString(), Level.INFO);
            this.logger.log("MESSAGE RESULT : ", Level.INFO);
            this.processReturn(this.messageResult);
            this.sendJsEnd();
            this.logger.log("----------END SEND OF THE OBJECT----------", Level.INFO);
        } else {
            this.logger.log("RESPONSE KO", Level.WARNING);
        }
        this.logger.log("----------END EDIT OBJECT----------", Level.INFO);
        return "ok";
    }
    
    public void sendJsMessage(String message)
    {
        JSObject jso;
        jso = JSObject.getWindow(this);
        jso.call("sendAppletMsg", new String[] {String.valueOf(message)});
    }
    
    public void sendJsEnd()
    {
        JSObject jso;
        jso = JSObject.getWindow(this);
        jso.call("endOfApplet", new String[] {String.valueOf(this.objectType), this.endMessage});
    }
    
    public void sendHttpRequest(String theUrl, String postRequest) throws Exception {
        URL UrlOpenRequest = new URL(theUrl);
        HttpURLConnection HttpOpenRequest = (HttpURLConnection) UrlOpenRequest.openConnection();
        HttpOpenRequest.setDoOutput(true);
        HttpOpenRequest.setRequestMethod("POST");
        if (!"none".equals(postRequest)) {
            OutputStreamWriter writer = new OutputStreamWriter(HttpOpenRequest.getOutputStream());
            writer.write("fileContent=" + this.fileContentTosend + "&fileExtension=" + this.fileExtension);
            writer.flush();
        }
        this.parse_xml(HttpOpenRequest.getInputStream());
        HttpOpenRequest.disconnect();
    }
}
