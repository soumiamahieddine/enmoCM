/** 
 * Jdk platform : 1.8 
 */

/** 
 * SVN version 141
 */

package com.maarch;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStreamWriter;
import java.lang.reflect.InvocationTargetException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLConnection;
import java.security.PrivilegedActionException;
import java.util.Hashtable;
import java.util.Iterator;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JApplet;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import netscape.javascript.JSException;
import org.w3c.dom.Document;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;
import netscape.javascript.JSObject;

/**
 * MaarchCM class manages webservices between end user desktop and Maarch
 * @author Laurent Giovannoni
 */
public class MaarchCM extends JApplet {
    //INIT PARAMETERS
    protected String url;
    protected String objectType;
    protected String objectTable;
    protected String objectId;
    protected String cookie;
    protected String userLocalDirTmp;
    
    protected String messageStatus;
    
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
    
    public MyLogger logger;
    
    public FileManager fM;
    public String fileToEdit;
    
    /**
     * Launch of the applet
     */
    public void init() throws JSException
    {
        System.out.println("----------BEGIN PARAMETERS----------");
        this.url = this.getParameter("url");
        this.objectType = this.getParameter("objectType");
        this.objectTable = this.getParameter("objectTable");
        this.objectId = this.getParameter("objectId");
        this.cookie = this.getParameter("cookie");
        
        System.out.println("URL : " + this.url);
        System.out.println("OBJECT TYPE : " + this.objectType);
        System.out.println("OBJECT TABLE : " + this.objectTable);
        System.out.println("OBJECT ID : " + this.objectId);
        System.out.println("COOKIE : " + this.cookie);
        
        System.out.println("----------CONTROL PARAMETERS----------");
        
        if (!this.controlParams()) {
            System.out.println("PARAMETERS NOT OK ! END OF APPLICATION");
            System.exit(0);
        }
        
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
    
    /**
     * Controls the applet parameters
     * @return boolean
     */
    public boolean controlParams()
    {
        Boolean returnControl = true;
        //URL
        
        try {
            URL url = new URL(this.url);
            URLConnection conn = url.openConnection();
            conn.connect();
        } catch (MalformedURLException e) {
            // the URL is not in a valid form
            System.out.println("the URL is not in a valid form " + this.url);
            returnControl = false;
        } catch (IOException e) {
            // the connection couldn't be established
            System.out.println("the connection couldn't be established " + this.url);
            returnControl = false;
        }
        
        //OBJECT TYPE
        if (
                !"template".equals(this.objectType) &&
                !"templateStyle".equals(this.objectType) &&
                !"attachmentVersion".equals(this.objectType) &&
                !"attachmentUpVersion".equals(this.objectType) &&
                !"resource".equals(this.objectType) &&
                !"attachmentFromTemplate".equals(this.objectType) &&
                !"attachment".equals(this.objectType) &&
                !"outgoingMail".equals(this.objectType)
        ) {
            System.out.println("ObjectType not in the authorized list " + this.objectType);
            returnControl = false;
        }
        
        //OBJECT TABLE
        if (
                !"res_letterbox".equals(this.objectTable) &&
                !"res_business".equals(this.objectTable) &&
                !"res_x".equals(this.objectTable) &&
                !"res_attachments".equals(this.objectTable) &&
                !"mlb_coll_ext".equals(this.objectTable) &&
                !"business_coll_ext".equals(this.objectTable) &&
                !"res_version_letterbox".equals(this.objectTable) &&
                !"res_version_business".equals(this.objectTable) &&
                !"res_version_x".equals(this.objectTable) &&
                !"res_view_attachments".equals(this.objectTable) &&
                !"res_view".equals(this.objectTable) &&
                !"res_view_letterbox".equals(this.objectTable) &&
                !"res_view_business".equals(this.objectTable) &&
                !"templates".equals(this.objectTable)
        ) {
            System.out.println("ObjectTable not in the authorized list " + this.objectTable);
            returnControl = false;
        }

        //OBJECT ID
        if (this.objectId.equals(null) || this.objectId.equals("")) {
            System.out.println("objectId is null or empty " + this.objectId);
            returnControl = false;
        }
        
        //COOKIE
        if (this.cookie.equals(null) || this.cookie.equals("")) {
            System.out.println("cookie is null or empty " + this.cookie);
            returnControl = false;
        }
        
        return returnControl;
        
    }
    
    /**
     * Retrieve the xml message from Maarch and parse it
     * @param flux_xml xml content message
     */
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
    
    /**
     * Manage the return of program execution
     * @param result result of the program execution
     */
    public void processReturn(Hashtable result) {
        Iterator itValue = result.values().iterator(); 
        Iterator itKey = result.keySet().iterator();
        while(itValue.hasNext()) {
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
            if ("COOKIE".equals(key)) {
                this.cookie = value;
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
            this.sendJsMessage(this.error);
        }
    }
    
    
    /**
     * Main function of the class
     * enables you to edit document with the user favorit editor
     */
    public String editObject() throws Exception, JSException {
        System.out.println("SECURE VERSION 2409 ----------BEGIN EDIT OBJECT----------LGI");
        System.out.println("----------BEGIN LOCAL DIR TMP IF NOT EXISTS----------");
        String os = System.getProperty("os.name").toLowerCase();
        boolean isUnix = os.contains("nix") || os.contains("nux");
        boolean isWindows = os.contains("win");
        boolean isMac = os.contains("mac");
        this.userLocalDirTmp = System.getProperty("user.home");
        
        this.fM = new FileManager();
        this.fM.createUserLocalDirTmp(this.userLocalDirTmp);
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
        
        this.fM.createUserLocalDirTmp(this.userLocalDirTmp);
        System.out.println("----------END LOCAL DIR TMP IF NOT EXISTS----------");
        
        
        System.out.println("Create the logger");
        this.logger = new MyLogger(this.userLocalDirTmp);
        
        this.logger.log("Delete thefile if exists", Level.INFO);
        this.fM.deleteFilesOnDir(this.userLocalDirTmp, "thefile");
        
        this.logger.log("----------BEGIN OPEN REQUEST----------", Level.INFO);
        String urlToSend = this.url + "?action=editObject&objectType=" + this.objectType
                        + "&objectTable=" + this.objectTable + "&objectId=" + this.objectId;
        sendHttpRequest(urlToSend, "none");
        this.logger.log("MESSAGE STATUS : " + this.messageStatus, Level.INFO);
        this.logger.log("MESSAGE RESULT : ", Level.INFO);
        this.processReturn(this.messageResult);
        this.logger.log("----------END OPEN REQUEST----------", Level.INFO);
        
        Integer randomNum;
        Integer minimum = 1;
        Integer maximum = 1000;
        
        randomNum = minimum + (int)(Math.random()*maximum); 
        this.fileToEdit = "thefile_" + randomNum + "." + this.fileExtension;
        
        this.logger.log("----------BEGIN CREATE THE BAT TO LAUNCH IF NECESSARY----------", Level.INFO);
        this.logger.log("create the file : "  + this.appPath, Level.INFO);
        this.fM.createBatFile(
            this.appPath, 
            this.userLocalDirTmp, 
            this.fileToEdit, 
            this.os,
            this.userLocalDirTmp
        );
        this.logger.log("----------END CREATE THE BAT TO LAUNCH IF NECESSARY----------", Level.INFO);
        
        if ("ok".equals(this.status)) {
            this.logger.log("RESPONSE OK", Level.INFO);
            
            this.logger.log("----------BEGIN EXECUTION OF THE EDITOR----------", Level.INFO);
            this.logger.log("CREATE FILE IN LOCAL PATH", Level.INFO);
            this.fM.createFile(this.fileContent, this.userLocalDirTmp + this.fileToEdit);

            Thread theThread;
            theThread = new Thread(new ProcessLoop(this));

            //theThread.logger = this.logger;

            theThread.start();
            
            String actualContent;
            this.fileContentTosend = "";
            do {
                theThread.sleep(1000);
                actualContent = FileManager.encodeFile(this.userLocalDirTmp + this.fileToEdit);
                if (!this.fileContentTosend.equals(actualContent)) {
                    this.fileContentTosend = actualContent;
                    this.logger.log("----------[SECURITY BACKUP] BEGIN SEND OF THE OBJECT----------", Level.INFO);
                    String urlToSave = this.url + "?action=saveObject&objectType=" + this.objectType 
                                + "&objectTable=" + this.objectTable + "&objectId=" + this.objectId;
                    this.logger.log("[SECURITY BACKUP] URL TO SAVE : " + urlToSave, Level.INFO);
                    sendHttpRequest(urlToSave, this.fileContentTosend);
                    this.logger.log("[SECURITY BACKUP] MESSAGE STATUS : " + this.messageStatus, Level.INFO);
                }
            }
            while (theThread.isAlive());
            
            theThread.interrupt();
            
            this.logger.log("----------END EXECUTION OF THE EDITOR----------", Level.INFO);
            
            this.logger.log("----------BEGIN RETRIEVE CONTENT OF THE OBJECT----------", Level.INFO);

            this.fileContentTosend = FileManager.encodeFile(this.userLocalDirTmp + this.fileToEdit);
            
            this.logger.log("----------END RETRIEVE CONTENT OF THE OBJECT----------", Level.INFO);
            
            String urlToSave = this.url + "?action=saveObject&objectType=" + this.objectType 
                            + "&objectTable=" + this.objectTable + "&objectId=" + this.objectId;
            this.logger.log("----------BEGIN SEND OF THE OBJECT----------", Level.INFO);
            this.logger.log("URL TO SAVE : " + urlToSave, Level.INFO);
            sendHttpRequest(urlToSave, this.fileContentTosend);
            this.logger.log("MESSAGE STATUS : " + this.messageStatus, Level.INFO);
            this.logger.log("LAST MESSAGE RESULT : ", Level.INFO);
            this.processReturn(this.messageResult);
            //send message to Maarch at the end
            if (!this.endMessage.isEmpty()) {
                this.sendJsMessage(this.endMessage);
            }
            this.sendJsEnd();
            this.logger.log("----------END SEND OF THE OBJECT----------", Level.INFO);
        } else {
            this.logger.log("RESPONSE KO", Level.WARNING);
        }
        this.logger.log("----------END EDIT OBJECT----------", Level.INFO);
        return "ok";
    }
    
    /**
     * Class to manage the execution of an external program
     */
    public class ProcessLoop extends Thread {
        public MaarchCM maarchCM;
        
        public ProcessLoop(MaarchCM maarchCM){
            this.maarchCM = maarchCM;
        }

        public void run() {
            try {
                maarchCM.launchProcess();
            } catch (PrivilegedActionException ex) {
                Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
            } catch (InterruptedException ex) {
                Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
            } catch (IllegalArgumentException ex) {
                Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
            } catch (IllegalAccessException ex) {
                Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
            } catch (InvocationTargetException ex) {
                Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
            }
        }
    }
    
    /**
     * Launch the external program and wait his execution end
     * @return boolean
     */
    public boolean launchProcess() throws PrivilegedActionException, InterruptedException, IllegalArgumentException, IllegalAccessException, InvocationTargetException
    {
        Process proc;

        this.logger.log("LAUNCH THE EDITOR !", Level.INFO);
        if ("linux".equals(this.os)) {
            proc = this.fM.launchApp(this.appPath);
        } else {
            this.logger.log("FILE TO EDIT : " + this.userLocalDirTmp + this.fileToEdit, Level.INFO);
            
            String programName;
            programName = this.fM.findGoodProgramWithExt(this.fileExtension);
            this.logger.log("PROGRAM NAME TO EDIT : " + programName, Level.INFO);
            String pathProgram;
            pathProgram = this.fM.findPathProgramInRegistry(programName);
            this.logger.log("PROGRAM PATH TO EDIT : " + pathProgram, Level.INFO);
            String options;
            options = this.fM.findGoodOptionsToEdit(this.fileExtension);
            this.logger.log("OPTION PROGRAM TO EDIT " + options, Level.INFO);
            String pathCommand;
            pathCommand = pathProgram + " " + options + "\""+this.userLocalDirTmp + this.fileToEdit+"\"";
            this.logger.log("PATH COMMAND TO EDIT " + pathCommand, Level.INFO);
            proc = this.fM.launchApp(pathCommand);
        }
        
        this.logger.log("WAIT END OF THE PROCESS", Level.INFO);
        proc.waitFor();
        this.logger.log("END OF THE PROCESS", Level.INFO);
        
        return true;
    }
    
    /**
     * Send a string message to Maarch with javascript
     * @param message
     */
    public void sendJsMessage(String message) throws JSException
    {
        JSObject jso;
        jso = JSObject.getWindow(this);
        this.logger.log("----------JS CALL sendAppletMsg TO MAARCH----------", Level.INFO);
        String theMessage;
        theMessage = String.valueOf(message);
        jso.call("sendAppletMsg", theMessage);
    }
    
    /**
     * Warns Maarch of the end of the execution of the applet
     */
    public void sendJsEnd() throws InterruptedException, JSException
    {
        JSObject jso;
        jso = JSObject.getWindow(this);
        this.logger.log("----------JS CALL endOfApplet TO MAARCH----------", Level.INFO);
        String[] theMessage = {String.valueOf(this.objectType), this.endMessage};
        jso.call("endOfApplet", (Object[]) theMessage);
    }
    
    /**
     * Send an http request to Maarch
     * @param url url to contact Maarch
     * @param postRequest the request
     */
    public void sendHttpRequest(String theUrl, String postRequest) throws Exception {
        URL UrlOpenRequest = new URL(theUrl);
        HttpURLConnection HttpOpenRequest = (HttpURLConnection) UrlOpenRequest.openConnection();
        HttpOpenRequest.setDoOutput(true);
        HttpOpenRequest.setRequestMethod("POST");
        HttpOpenRequest.setRequestProperty("Cookie", this.cookie);
        if (!"none".equals(postRequest)) {
            OutputStreamWriter writer = new OutputStreamWriter(HttpOpenRequest.getOutputStream());
            writer.write("fileContent=" + this.fileContentTosend + "&fileExtension=" + this.fileExtension);
            writer.flush();
        } else {
            OutputStreamWriter writer = new OutputStreamWriter(HttpOpenRequest.getOutputStream());
            writer.write("foo=bar");
            writer.flush();
        }
        this.parse_xml(HttpOpenRequest.getInputStream());
        HttpOpenRequest.disconnect();
    }
}
