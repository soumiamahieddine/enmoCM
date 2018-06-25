/**
 * Jdk platform : 1.8
 */

/**
 * SVN version 141
 */

package com.maarch;

//import java.applet.Applet;
import java.awt.AWTException;
import java.awt.Image;
import java.awt.MenuItem;
import java.awt.PopupMenu;
import java.awt.SystemTray;
import java.awt.Toolkit;
import java.awt.TrayIcon;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.io.*;
import java.lang.reflect.InvocationTargetException;
import java.net.MalformedURLException;
import java.net.URL;
import java.nio.file.FileSystems;
import java.nio.file.Path;
import java.nio.file.Paths;
import static java.nio.file.StandardWatchEventKinds.ENTRY_CREATE;
import static java.nio.file.StandardWatchEventKinds.ENTRY_DELETE;
import static java.nio.file.StandardWatchEventKinds.ENTRY_MODIFY;
import java.nio.file.WatchEvent;
import java.nio.file.WatchKey;
import java.nio.file.WatchService;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.PrivilegedActionException;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.Hashtable;
import java.util.Iterator;
import java.util.List;
import java.util.Set;
import java.util.logging.Level;
import java.util.logging.Logger;
//import javax.swing.JApplet;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;

import netscape.javascript.JSException;
import org.apache.http.client.config.CookieSpecs;
import org.apache.http.client.config.RequestConfig;
import org.apache.http.client.methods.CloseableHttpResponse;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.client.protocol.HttpClientContext;
import org.apache.http.entity.AbstractHttpEntity;
import org.apache.http.impl.client.*;
import org.apache.http.impl.cookie.BasicClientCookie;
import org.w3c.dom.Document;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

import javax.swing.JOptionPane;
import org.apache.http.NameValuePair;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.message.BasicNameValuePair;

/**
 * MaarchCM class manages webservices between end user desktop and Maarch
 * @author maarch and DIS
 */
public class MaarchCM {

    //INIT PARAMETERS
    protected String url;
    protected String idApplet;
    protected String objectType;
    protected String objectTable;
    protected String objectId;
    protected String cookie;
    protected String clientSideCookies;
    protected String uniqueId;
    protected String convertPdf;
    protected String onlyConvert;
    protected String md5File;

    protected String domain;
    protected String userLocalDirTmp;
    protected String userMaarch;
    protected String messageStatus;
    static Hashtable messageResult = new Hashtable();

    //XML PARAMETERS
    protected String status;
    protected String appPath;
    protected String appPath_convert;
    protected String fileContent;
    protected String fileContentVbs;
    protected String vbsPath;
    protected String fileContentExe;
    protected String useExeConvert;
    protected String fileExtension;
    protected String error;
    protected String endMessage;
    protected String os;
    protected String fileContentTosend;
    protected String pdfContentTosend;

    private  final HttpClientContext httpContext = HttpClientContext.create();
    private CloseableHttpClient httpClient; // Apache HttpClient yet to be instantiated

    public MyLogger logger;
    public FileManager fM;
    public String fileToEdit;
    public String editMode;    
    public String programName;
    
    SystemTray tray = SystemTray.getSystemTray();
    //If the icon is a file
    Image image = Toolkit.getDefaultToolkit().createImage(this.getClass().getResource("logo_only.png"));
    //Alternative (if the icon is on the classpath):

    ActionListener exitListener = new ActionListener() {
        public void actionPerformed(ActionEvent e) {
            System.out.println("Exiting...");
            try {
                endRequestApplet();
            } catch (UnsupportedEncodingException ex) {
                Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
            } catch (InterruptedException ex) {
                Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
            }
            System.exit(0);
        }
    };
    TrayIcon trayIcon = new TrayIcon(image, "Tray Demo");


    
    public List<String> fileToDelete = new ArrayList<String>();
    
    
    public static void main(String[] args) throws JSException, AWTException, InterruptedException, IOException {
            MaarchCM MaarchCM = new MaarchCM();
            MaarchCM.start(args);
    }
    
   
    /**
     * Launch of the JNLP
     */
    public void start(String[] args) throws JSException, AWTException, InterruptedException, IOException {
        
    
        PopupMenu popup = new PopupMenu();
        MenuItem defaultItem = new MenuItem("Fermer l'applet");
        defaultItem.addActionListener(exitListener);
        popup.add(defaultItem);
        //Let the system resize the image if needed
        trayIcon.setImageAutoSize(true);
        //Set tooltip text for the tray icon
        trayIcon.setToolTip("Maarch content editor");
        tray.add(trayIcon);
        
        trayIcon.setPopupMenu(popup);
                
        initDatas(args);
        
        initHttpRequest();
        
        getClientEnv();
        
        try {
            //editObject();
            if (onlyConvert.equals("true")) {
               launchOnlyConvert(); 
            } else {
                editObject_v2();
            }
            
            System.exit(0);
        } catch (Exception ex) {
            Logger.getLogger(MaarchCM.class.getName()).log(Level.SEVERE, null, ex);
        }
    }

    public void initDatas(String[] args) {
        int index;

        for (index = 0; index < args.length; ++index)
        {
            System.out.println("args[" + index + "]: " + args[index]);
        }
        url = args[0];
        objectType = args[1];
        objectTable = args[2];
        objectId = args[3];
        uniqueId = args[4];
        cookie = args[5];
        clientSideCookies = args[6];
        idApplet = args[7];
        userMaarch = args[8];
        convertPdf = args[9];
        onlyConvert = args[10];
        md5File = args[11];

        System.out.println("URL : " + url);
        System.out.println("OBJECT TYPE : " + objectType);
        System.out.println("ID APPLET : " + idApplet);
        System.out.println("OBJECT TABLE : " + objectTable);
        System.out.println("OBJECT ID : " + objectId);
        System.out.println("UNIQUE ID : " + uniqueId);
        System.out.println("COOKIE : " + cookie);
        System.out.println("CLIENTSIDECOOKIES : " + clientSideCookies);
        System.out.println("USERMAARCH : " + userMaarch);
        System.out.println("CONVERTPDF : " + convertPdf);
        System.out.println("ONLYCONVERT : " + onlyConvert);
        System.out.println("MD5FILE : " + md5File);
        System.out.println("----------CONTROL PARAMETERS----------");
    }
    public void getClientEnv() throws InterruptedException, IOException {
        os = System.getProperty("os.name").toLowerCase();
        boolean isUnix = os.contains("nix") || os.contains("nux");
        boolean isWindows = os.contains("win");
        boolean isMac = os.contains("mac");
        if (isWindows) {
            System.out.println("This is Windows");
            os = "win";
        } else if (isMac) {
            System.out.println("This is Mac");
            os = "mac";
        } else if (isUnix) {
            System.out.println("This is Unix or Linux");
            os = "linux";
        } else {
            System.out.println("Your OS is not supported!!");
        }
        fM = new FileManager();
        String userLocalDir = System.getProperty("user.home");
        userLocalDirTmp = userLocalDir + File.separator + "maarchTmp";
        
        System.out.println("Create the logger");
        logger = new MyLogger(userLocalDirTmp + File.separator);
        
        System.out.println("APP PATH: " + appPath);
        System.out.println("----------BEGIN LOCAL DIR TMP IF NOT EXISTS----------");

        String info = fM.createUserLocalDirTmp(userLocalDirTmp, os);

        if (info == "ERROR") {
            logger.log("ERREUR : Permissions insuffisante sur votre répertoire temporaire maarch", Level.SEVERE);
            messageStatus = "ERROR";
            messageResult.clear();
            messageResult.put("ERROR", "Permissions insuffisante sur votre répertoire temporaire maarch");
            processReturn(messageResult);
        }
    }
    
    public void initHttpRequest() {
        if (
                isURLInvalid() ||
                isObjectTypeInvalid() ||
                isObjectTableInvalid() ||
                isObjectIdInvalid() ||
                isCookieInvalid()
        ) {
            System.out.println("PARAMETERS NOT OK ! END OF APPLICATION");
            //System.exit(0);
            try {
                //MaarchCM.getAppletContext().showDocument(new URL("error.html"));
                //Go to an appropriate error page
            } catch (Exception e) {
                //Nothing
            }
        }

        System.out.println("----------END PARAMETERS----------");
        
        if ("empty".equals(uniqueId)) {
            uniqueId = null;
        }
        
        if ("empty".equals(clientSideCookies)) {
            clientSideCookies = null;
        }
        
        // The following code is to ensure a high level of management for HTTP cookies
        BasicCookieStore cookieStore = new BasicCookieStore();
        // Loading the cookie store with the Maarch cookie provided by the server
        cookieStore.addCookie(getCookieFromString(cookie));
        if (
                clientSideCookies != null && 
                clientSideCookies.length() > 0
        ) {
            System.out.println("clientSideCookies : " + clientSideCookies);
            // Within the whole cookie string returned from JavaScript, cookies are separated by a semicolon followed by a space
            // Let's get an array where cookies are stored with a "name=value" pattern
            String[] cookies = clientSideCookies.split(";\\s");
            System.out.println("cookies : " + cookies);
            // Iterate through the cookie array to retrieve each cookie name ans value and load the cookie store
            for (String nameValue : cookies) {
                cookieStore.addCookie(getCookieFromString(nameValue)); // Loading the cookie store
            }
        }
        httpContext.setCookieStore(cookieStore); // Assign all the cookies retrieved from JavaScript
        // Apply a Cookie policy: https://hc.apache.org/httpcomponents-client-ga/tutorial/html/statemgmt.html
        RequestConfig globalConfig = RequestConfig.custom().setCookieSpec(CookieSpecs.DEFAULT).build();

        // Pick up the best Apache HttpClient to do the job
        // which will allows for the automatic retrieval of the user session Kerberos ticket,
        // so this app will be able to properly talk with a proxy
        if ("win".equals(os) && WinHttpClients.isWinAuthAvailable()) {
            // Instantiation of the Apache HttpClient for Windows 7
            httpClient = WinHttpClients.custom().setDefaultRequestConfig(globalConfig).setDefaultCookieStore(cookieStore).build();
            System.out.println("The Apache HttpClient for Windows 7 was picked up");
        } else {
            // Instantiation of the generic Apache HttpClient
            HttpClientBuilder httpClientBuilder = HttpClients.custom();
            httpClientBuilder.useSystemProperties();
            httpClientBuilder.setDefaultRequestConfig(globalConfig);
            httpClientBuilder.setDefaultCookieStore(cookieStore);
            httpClient = httpClientBuilder.build();
            System.out.println("The generic Apache HttpClient was picked up");
        }

        if (httpClient == null) {
            System.out.println("NO HTTP CLIENT WAS INSTANTIATED, THE APPLICATION WILL FAIL!");
        }
    }
    
    /**
     * Controls the url parameter
     * @return boolean
     */
    private boolean isURLInvalid() {
        try {
            URL address = new URL(url); // Trying to build a valid URL
            address.openConnection().connect(); // Trying to open a valid connection
            domain = address.getHost(); // Retrieve the domain used
            System.out.println("DOMAIN USED IS: " + domain);
            return false; //success
        } catch (MalformedURLException e) {
            System.out.println("the URL is not a valid form " + url);
        } catch (IOException e) {
            System.out.println("the connection couldn't be etablished " + url);
        }
        return true; //default is failure
    }

    /**
     * Controls the objectType parameter
     * @return boolean
     */
    private boolean isObjectTypeInvalid() {
        Set<String> whiteList = new HashSet<>();
        whiteList.add("template");
        whiteList.add("templateStyle");
        whiteList.add("attachmentVersion");
        whiteList.add("attachmentUpVersion");
        whiteList.add("resource");
        whiteList.add("attachmentFromTemplate");
        whiteList.add("attachment");
        whiteList.add("outgoingMail");
        if (whiteList.contains(objectType)) return false; //success
        System.out.println("ObjectType not in the authorized list " + objectType);
        return true; //default is failure
    }

    /**
     * Controls the objectTable parameter
     * @return boolean
     */
    private boolean isObjectTableInvalid() {
        Set<String> whiteList = new HashSet<>();
        whiteList.add("res_letterbox");
        whiteList.add("res_attachments");
        whiteList.add("mlb_coll_ext");
        whiteList.add("res_version_letterbox");
        whiteList.add("res_view_attachments");
        whiteList.add("res_view_letterbox");
        whiteList.add("templates");
        if (whiteList.contains(objectTable)) return false; //success
        System.out.println("objectTable not in the authorized list " + objectTable);
        return true; //default is failure
    }

    /**
     * Controls the objectId parameter
     * @return boolean
     */
    private boolean isObjectIdInvalid() {
        if (objectId != null && objectId.length() > 0) return false; //success
        System.out.println("objectId is null or empty " + objectId);
        return true; //default is failure
    }

    /**
     * Controls the cookie parameter
     * @return boolean
     */
    private boolean isCookieInvalid() {
        if (cookie != null && cookie.length() > 0) return false; //success
        System.out.println("cookie is null or empty " + cookie);
        return true; //default is failure
    }

    /**
     * Build a cookie from a String
     * @param nameValue
     * @return BasicClientCookie
     */
    private BasicClientCookie getCookieFromString(String nameValue) {
        int separator = nameValue.indexOf('='); // Locating the equal character
        String name = nameValue.substring(0, separator); // Getting everything before the equal character
        String value = nameValue.substring(separator + 1); // Getting everything after the equal character
        BasicClientCookie cookie = new BasicClientCookie(name, value);
        cookie.setPath("/");
        cookie.setDomain(domain);
        return cookie;
    }

    public void createPDF(String docxFile, String directory, String os) {
        logger.log("createPDF ", Level.INFO);
        try {
            System.out.println("mode ! : " + editMode);
            //patch onlyConvert
            if (onlyConvert.equals("true")) {
                if ("linux".equals(os) || "mac".equals(os)) {
                    editMode = "libreoffice";
                } else {
                    programName = fM.findGoodProgramWithExt(fileExtension);
                    String pathProgram;
                    pathProgram = fM.findPathProgramInRegistry(programName);
                    System.out.println("check prog name : "+programName);
                    System.out.println("check path : "+pathProgram);
                    if("soffice.exe".equals(programName)){   
                        if("\"null\"".equals(pathProgram)){
                            System.out.println(programName+" not found! switch to microsoft office...");
                            programName = "office.exe";
                        }
                    }else{
                        if("\"null\"".equals(pathProgram)){
                            System.out.println(programName+" not found! switch to libreoffice...");
                            programName = "soffice.exe";
                        }
                    }
                    if("soffice.exe".equals(programName)){
                        editMode = "libreoffice";
                    }else{
                        editMode = "office"; 
                    }
                }
            }
            boolean conversion = true;
            String cmd = "";
            if (docxFile.contains(".odt") || docxFile.contains(".ods") || docxFile.contains(".ODT") || docxFile.contains(".ODS")) {
                logger.log("This is opendocument ! ", Level.INFO);
                if (os == "linux") {
                    cmd = "libreoffice -env:UserInstallation=file://"+userLocalDirTmp + File.separator + idApplet+"_conv/ --headless --convert-to pdf --outdir \"" + userLocalDirTmp + "\" \"" + docxFile + "\"";
                } else if (os == "mac") {
                    cmd = "cd /Applications/LibreOffice.app/Contents/MacOs && ./soffice --headless --convert-to pdf:writer_pdf_Export --outdir \"" + userLocalDirTmp + "\" \"" + docxFile + "\"";
                } else {
                    String convertProgram;
                    convertProgram = fM.findPathProgramInRegistry("soffice.exe");
                    cmd = convertProgram + " --headless --convert-to pdf --outdir \"" + userLocalDirTmp + "\" \"" + docxFile + "\" \r\n";
                }

            } else if (docxFile.contains(".doc") || docxFile.contains(".docx") || docxFile.contains(".DOC") || docxFile.contains(".DOCX")) {
                logger.log("This is MSOffice document ", Level.INFO);
                if (useExeConvert.equals("false")) {
                    if (os == "linux") {
                        cmd = "libreoffice --headless --convert-to pdf --outdir \"" + userLocalDirTmp + "\" \"" + docxFile + "\"";
                    }  else if (os == "mac") {
                        cmd = "cd /Applications/LibreOffice.app/Contents/MacOs && ./soffice --headless --convert-to pdf:writer_pdf_Export --outdir \"" + userLocalDirTmp + "\" \"" + docxFile + "\"";
                    } else if(editMode.equals("libreoffice")){
                        String convertProgram;
                        convertProgram = fM.findPathProgramInRegistry("soffice.exe");
                        cmd = convertProgram + " --headless --convert-to pdf --outdir \"" + userLocalDirTmp + "\" \"" + docxFile + "\" \r\n";
                    }else{
                        vbsPath = userLocalDirTmp + File.separator + "DOC2PDF_VBS.vbs";
                        fM.createFile(fileContentVbs, vbsPath);
                        fileToDelete.add(vbsPath);
                        cmd = "cmd /C c:\\Windows\\System32\\cscript \"" + vbsPath + "\" \"" + docxFile + "\" /nologo \r\n";      
                    }
                }
            } else {
                conversion = false;
            }

            if (conversion) {
                Process proc_vbs;
                appPath_convert = userLocalDirTmp + File.separator + "conversion_"+idApplet+".sh";
                fileToDelete.add(appPath_convert);
                logger.log("EXEC PATH : " + cmd, Level.INFO);
                if (os == "linux" || os == "mac") {
                    final Writer outBat;
                    outBat = new OutputStreamWriter(new FileOutputStream(appPath_convert), "CP850");
                    logger.log("--- cmd sh  --- " + cmd, Level.INFO);
                    outBat.write(cmd);
                    outBat.close();

                    File myFileBat = new File(appPath_convert);
                    myFileBat.setReadable(true, false);
                    myFileBat.setWritable(true, false);
                    myFileBat.setExecutable(true, false);

                    final String exec_vbs = "\"" + appPath + "\"";
                    proc_vbs = fM.launchApp(appPath_convert);
                } else {
                    proc_vbs = fM.launchApp(cmd);
                }
                
                proc_vbs.waitFor();
            }

        } catch (Throwable e) {
            logger.log("Erreur ! : " + e, Level.SEVERE);
            e.printStackTrace();
        }
    }

    /**
     * Retrieve the xml message from Maarch and parse it
     * @param flux_xml xml content message
     */
    public void parse_xml(InputStream flux_xml) throws SAXException, IOException, ParserConfigurationException, InterruptedException {
        logger.log("----------BEGIN PARSE XML----------", Level.INFO);
        DocumentBuilder builder = DocumentBuilderFactory.newInstance().newDocumentBuilder();

        try {
            Document doc = builder.parse(flux_xml);
            messageResult.clear();
            NodeList level_one_list = doc.getChildNodes();
            for (Integer i = 0; i < level_one_list.getLength(); i++) {
                NodeList level_two_list = level_one_list.item(i).getChildNodes();
                if ("SUCCESS".equals(level_one_list.item(i).getNodeName())) {
                    for (Integer j = 0; j < level_one_list.item(i).getChildNodes().getLength(); j++) {
                        messageResult.put(level_two_list.item(j).getNodeName(), level_two_list.item(j).getTextContent());
                    }
                    messageStatus = "SUCCESS";
                } else if ("ERROR".equals(level_one_list.item(i).getNodeName())) {
                    for (Integer j = 0; j < level_one_list.item(i).getChildNodes().getLength(); j++) {
                        messageResult.put(level_two_list.item(j).getNodeName(), level_two_list.item(j).getTextContent());
                    }
                    messageStatus = "ERROR";
                }
            }
        } catch (SAXException | IOException e) {

            logger.log("ERREUR : Le document n'a pas pu être transféré du coté client. Assurez-vous que le modèle n'est pas corrompu et que la zone de stockage des templates soit correct.", Level.SEVERE);
            messageStatus = "ERROR";
            messageResult.put("ERROR", "Le document n'a pas pu être transféré du coté client. Assurez-vous que le modèle n'est pas corrompu et que la zone de stockage des templates soit correct.");
            processReturn(messageResult);
        }
        logger.log("----------END PARSE XML----------", Level.INFO);
    }

    /**
     * Manage the return of program execution
     * @param result result of the program execution
     */
    public void processReturn(Hashtable result) throws InterruptedException, UnsupportedEncodingException {
        Iterator itValue = result.values().iterator();
        Iterator itKey = result.keySet().iterator();
        while (itValue.hasNext()) {
            String value = (String) itValue.next();
            String key = (String) itKey.next();

            logger.log(key + " : " + value, Level.INFO);
            if ("STATUS".equals(key)) status = value;
            if ("OBJECT_TYPE".equals(key)) objectType = value;
            if ("OBJECT_TABLE".equals(key)) objectTable = value;
            if ("OBJECT_ID".equals(key)) objectId = value;
            if ("UNIQUE_ID".equals(key)) uniqueId = value;
            if ("COOKIE".equals(key)) cookie = value;
            if ("CLIENTSIDECOOKIES".equals(key)) clientSideCookies = value;
            if ("APP_PATH".equals(key)) ; //appPath = value;
            if ("FILE_CONTENT".equals(key)) fileContent = value;
            if ("FILE_CONTENT_VBS".equals(key)) fileContentVbs = value;
            if ("VBS_PATH".equals(key)) vbsPath = value;
            if ("FILE_CONTENT_EXE".equals(key)) fileContentExe = value;
            if ("USE_EXE_CONVERT".equals(key)) useExeConvert = value;
            if ("FILE_EXTENSION".equals(key)) fileExtension = value;
            if ("ERROR".equals(key)) error = value;
            if ("END_MESSAGE".equals(key)) endMessage = value;
        }
        //send message error to Maarch if necessary
        if (!error.isEmpty()) {
            endRequestApplet();
            trayIcon.displayMessage("Maarch content editor", error, TrayIcon.MessageType.ERROR);
            Thread.sleep(5000);
            System.exit(0);
        }
    }

    /**
     * Launch the external program and wait his execution end
     * @return boolean
     */
    public Boolean launchProcess() throws PrivilegedActionException, InterruptedException, IllegalArgumentException, IllegalAccessException, InvocationTargetException {
        logger.log("LAUNCH THE EDITOR !", Level.INFO);
        
        if ("linux".equals(os)) {
            editMode = "libreoffice";
            fM.launchApp("libreoffice --nolockcheck --nodefault --nofirststartwizard --nofirststartwizard --norestore " + userLocalDirTmp + File.separator + fileToEdit);
        } else if ("mac".equals(os)) {
            editMode = "libreoffice";
            fM.launchApp("open -W " + userLocalDirTmp + File.separator + fileToEdit);
        } else {
            logger.log("FILE TO EDIT : " + userLocalDirTmp + fileToEdit, Level.INFO);

            programName = fM.findGoodProgramWithExt(fileExtension);
            String pathProgram;
            pathProgram = fM.findPathProgramInRegistry(programName);
            String options;
            System.out.println("check prog name : "+programName);
            System.out.println("check path : "+pathProgram);
            if("soffice.exe".equals(programName)){   
                if("\"null\"".equals(pathProgram)){
                    System.out.println(programName+" not found! switch to microsoft office...");
                    programName = "office.exe";
                    pathProgram = fM.findPathProgramInRegistry(programName);
                    options = fM.findGoodOptionsToEdit(fileExtension);
                }else{
                    options = " --nolockcheck --nodefault --nofirststartwizard --nofirststartwizard --norestore ";          
                }
            }else{
                if("\"null\"".equals(pathProgram)){
                    System.out.println(programName+" not found! switch to libreoffice...");
                    programName = "soffice.exe";
                    pathProgram = fM.findPathProgramInRegistry(programName);
                    options = " --nolockcheck --nodefault --nofirststartwizard --nofirststartwizard --norestore ";
                }else{
                    options = fM.findGoodOptionsToEdit(fileExtension);
                }
            }
            
            if("soffice.exe".equals(programName)){
                editMode = "libreoffice";
            }else{
                editMode = "office"; 
            }
            logger.log("PROGRAM NAME TO EDIT : " + programName, Level.INFO);
            logger.log("OPTION PROGRAM TO EDIT " + options, Level.INFO);
            logger.log("PROGRAM PATH TO EDIT : " + pathProgram, Level.INFO);
            
            
            String pathCommand;
            pathCommand = pathProgram + " " + options + "\"" + userLocalDirTmp + File.separator + fileToEdit + "\"";
            logger.log("PATH COMMAND TO EDIT " + pathCommand, Level.INFO);
            fM.launchApp(pathCommand);
        }
        return true;
    }

    /**
     * Send an http request to Maarch
     * @param theUrl url to contact Maarch
     * @param postRequest the request
     * @param endProcess end request
     */
    public void sendHttpRequest(String theUrl, final String postRequest, final boolean endProcess) throws UnsupportedEncodingException {
        System.out.println("URL request : " + theUrl);

        // Inner class representing the payload to be posted via HTTP
        AbstractHttpEntity entity = new AbstractHttpEntity() {
            public boolean isRepeatable() {
                return false; // must be implemented
            }

            public long getContentLength() {
                return -1; // must be implemented
            }

            public boolean isStreaming() {
                return false; // must be implemented
            }

            public InputStream getContent() throws IOException {
                return new ByteArrayInputStream(postRequest.getBytes());
            }

            public void writeTo(OutputStream out) throws IOException {
                System.out.println("METHOD 'WriteTo' WAS CALLED!");
                if (!"none".equals(postRequest)) {
                    Writer writer = new OutputStreamWriter(out, "UTF-8");
                    // Using a StringBuffer rather than multiple "+" operators results in much better performance!
                    StringBuffer sb = new StringBuffer();
                    if ("true".equals(convertPdf)) {
                        if (endProcess) {
                            // Prepending "null" saves from testing "if(pdfContentTosend != null)"
                            if ("null".equalsIgnoreCase(pdfContentTosend)) {
                                sb.append("fileContent=");
                                sb.append(fileContentTosend);
                                sb.append("&fileExtension=");
                                sb.append(fileExtension);
                            } else {
                                sb.append("fileContent=");
                                sb.append(fileContentTosend);
                                sb.append("&fileExtension=");
                                sb.append(fileExtension);
                                sb.append("&pdfContent=");
                                sb.append(pdfContentTosend);
                            }
                        } else {
                            sb.append("fileContent=");
                            sb.append(fileContentTosend);
                            sb.append("&fileExtension=");
                            sb.append(fileExtension);
                        }
                    } else {
                        sb.append("fileContent=");
                        sb.append(fileContentTosend);
                        sb.append("&fileExtension=");
                        sb.append(fileExtension);
                    }
                    
                    writer.write(sb.toString());
                    writer.flush();
                }
            }
        };
        HttpPost request = new HttpPost(theUrl); // Construct a HTTP post request
        System.out.println("BUILT REQUEST: " + request);
        
        
        // Request parameters and other properties.
        List<NameValuePair> params = new ArrayList<NameValuePair>(2);
        
        if ("true".equals(convertPdf)) {
            if (endProcess) {
                // Prepending "null" saves from testing "if(pdfContentTosend != null)"
                if ("null".equalsIgnoreCase(pdfContentTosend)) {
                    params.add(new BasicNameValuePair("fileContent", fileContentTosend));
                    params.add(new BasicNameValuePair("fileExtension", fileExtension));
                } else {
                    params.add(new BasicNameValuePair("fileContent", fileContentTosend));
                    params.add(new BasicNameValuePair("fileExtension", fileExtension));
                    params.add(new BasicNameValuePair("pdfContent", pdfContentTosend));
                }
            } else {
                params.add(new BasicNameValuePair("fileContent", fileContentTosend));
                params.add(new BasicNameValuePair("fileExtension", fileExtension));
            }
        } else {
            params.add(new BasicNameValuePair("fileContent", fileContentTosend));
            params.add(new BasicNameValuePair("fileExtension", fileExtension));
        }
        
        request.setEntity(new UrlEncodedFormEntity(params, "UTF-8"));
        System.out.println("FULL REQUEST" + request);
        try {
            System.out.println("COOKIES TO BE SENT: " + httpContext.getCookieStore().getCookies()); // Show the cookies to be sent
            CloseableHttpResponse response = httpClient.execute(request, httpContext); // Carry out the HTTP post request
            System.out.println(response);
            if (response == null) {
                System.out.println("NO RESPONSE, THE APPLICATION WILL FAIL!");
            } else {
                parse_xml(response.getEntity().getContent()); // Process the response from the server
                response.close();
            }
        } catch (Exception ex) {
            logger.log("erreur: " + ex, Level.SEVERE);
            trayIcon.displayMessage("Maarch content editor", "La connexion au serveur a été interrompue, le document édité n'a pas été sauvegardé !", TrayIcon.MessageType.ERROR);
        }
    }
    
    public void editObject_v2() throws InterruptedException, IOException, PrivilegedActionException, IllegalArgumentException, IllegalAccessException, InvocationTargetException, Exception {
        String urlToSend;
        if (checksumFile(md5File) == false) {
            logger.log("The file is not here in maarchTmp folder.", Level.INFO);
            logger.log("RETRIEVE DOCUMENT ...", Level.INFO);
            
            urlToSend = url + "?action=editObject&objectType=" + objectType
                + "&objectTable=" + objectTable + "&objectId=" + objectId
                + "&uniqueId=" + uniqueId;

            logger.log("FIRST URL CALL : " + urlToSend, Level.INFO);
            sendHttpRequest(urlToSend, "none", false);
            logger.log("MESSAGE STATUS : " + messageStatus, Level.INFO);
            logger.log("MESSAGE RESULT : ", Level.INFO);
            processReturn(messageResult);
            logger.log("CREATE FILE IN LOCAL PATH", Level.INFO);

            //fileToEdit = "thefile_" + idApplet + "." + fileExtension;
            fileToEdit = md5File + "." + fileExtension;

            fM.createFile(fileContent, userLocalDirTmp + File.separator + fileToEdit);
        }   
        
        //fileToDelete.add(userLocalDirTmp + File.separator + fileToEdit);
        fileContentTosend = "";
                        
        launchProcess();
        try {
            WatchService watcher = FileSystems.getDefault().newWatchService();
            
            Path dir = Paths.get(userLocalDirTmp);
            dir.register(watcher, ENTRY_CREATE, ENTRY_DELETE, ENTRY_MODIFY);
            String editor = "";

            while (true) {
                WatchKey key;
                try {
                    key = watcher.take();
                } catch (InterruptedException ex) {
                    return;
                }
                 
                for (WatchEvent<?> event : key.pollEvents()) {
                    WatchEvent.Kind<?> kind = event.kind();
                     
                    @SuppressWarnings("unchecked")
                    WatchEvent<Path> ev = (WatchEvent<Path>) event;
                    Path fileName = ev.context();
                     
                    //System.out.println(kind.name() + ": " + fileName);

                    if (kind == ENTRY_CREATE && fileName.toString().equals(".~lock." + fileToEdit + "#")) {
                        editor = "libreoffice";
                    }
                    if (kind == ENTRY_CREATE && fileName.toString().equals("~$" + fileToEdit.substring(2, fileToEdit.length())) ) {
                        editor = "office";
                    }
                    if (kind == ENTRY_MODIFY && fileName.toString().equals(fileToEdit)) {
                        System.out.println("Fichier modifié!!!");
                        Thread.sleep(3000);
                        File fileTotest = new File(userLocalDirTmp + File.separator + fileToEdit);
                        if (fileTotest.canRead()) {
                            String actualContent = FileManager.encodeFile(userLocalDirTmp + File.separator + fileToEdit);
                            if (!fileContentTosend.equals(actualContent)) {
                                fileContentTosend = actualContent;
                                logger.log("----------[SECURITY BACKUP] BEGIN SEND OF THE OBJECT----------", Level.INFO);
                                String urlToSave = url + "?action=saveObject&objectType=" + objectType
                                        + "&objectTable=" + objectTable + "&objectId=" + objectId
                                        + "&uniqueId=" + uniqueId + "&step=backup&userMaarch=" + userMaarch;
                                logger.log("[SECURITY BACKUP] URL TO SAVE : " + urlToSave, Level.INFO);
                                trayIcon.displayMessage("Maarch content editor", "Envoi du brouillon ...", TrayIcon.MessageType.INFO);
                                sendHttpRequest(urlToSave, fileContentTosend, false);
                                logger.log("[SECURITY BACKUP] MESSAGE STATUS : " + messageStatus, Level.INFO);
                            }
                        } else {
                            logger.log(userLocalDirTmp + fileToEdit + " FILE NOT READABLE !!!!!!", Level.INFO);
                        }
                    }
                    if (kind == ENTRY_CREATE && (fileName.toString().equals(".~lock." + fileToEdit + "#") || fileName.toString().equals("~$" + fileToEdit.substring(2, fileToEdit.length())))) {
                        System.out.println("Fichier fichier en cours d'édition ...");
                        logger.log("----------BEGIN OPEN REQUEST----------", Level.INFO);

                        urlToSend = url + "?action=editObject&objectType=" + objectType
                            + "&objectTable=" + objectTable + "&objectId=" + objectId
                            + "&uniqueId=" + uniqueId;


                        logger.log("FIRST URL CALL : " + urlToSend, Level.INFO);
                        sendHttpRequest(urlToSend, "none", false);
                        logger.log("MESSAGE STATUS : " + messageStatus, Level.INFO);
                        logger.log("MESSAGE RESULT : ", Level.INFO);
                        processReturn(messageResult);
                        logger.log("----------END OPEN REQUEST----------", Level.INFO);
                        
                    }

                    if (kind == ENTRY_DELETE && (fileName.toString().equals(".~lock." + fileToEdit + "#") || fileName.toString().equals("~$" + fileToEdit.substring(2, fileToEdit.length())))) {
                        Thread.sleep(500);
                        File fileTotest = new File(userLocalDirTmp + File.separator +".~lock." + fileToEdit + "#");
                        if(!fileTotest.exists() || editor.equals("office")) {
                           System.out.println("Fermeture de l'éditeur..."); 
                           logger.log("----------END EXECUTION OF THE EDITOR----------", Level.INFO);

                            logger.log("----------BEGIN RETRIEVE CONTENT OF THE OBJECT----------", Level.INFO);

                            fileContentTosend = FileManager.encodeFile(userLocalDirTmp + File.separator + fileToEdit);

                            logger.log("----------END RETRIEVE CONTENT OF THE OBJECT----------", Level.INFO);

                            logger.log("conversion pdf ? " + convertPdf , Level.INFO);

                            if ("true".equals(convertPdf)) {
                                if ((fileExtension.equalsIgnoreCase("docx") || fileExtension.equalsIgnoreCase("doc") || fileExtension.equalsIgnoreCase("docm") || fileExtension.equalsIgnoreCase("odt") || fileExtension.equalsIgnoreCase("ott"))) {
                                    logger.log("----------CONVERSION PDF----------", Level.INFO);
                                    //String pdfFile = userLocalDirTmp + File.separator + "thefile_" + idApplet + ".pdf";
                                    String pdfFile = userLocalDirTmp + File.separator + md5File + ".pdf";                                    
                                    createPDF(userLocalDirTmp + File.separator + fileToEdit, userLocalDirTmp, os);
                                    File file=new File(pdfFile);
                                    if (file.exists()) {
                                        pdfContentTosend = FileManager.encodeFile(pdfFile);
                                        fileToDelete.add(pdfFile);
                                        
                                    } else {
                                        pdfContentTosend = "null";
                                        logger.log("ERREUR DE CONVERSION PDF !", Level.WARNING); 
                                    }
                                    
                                    logger.log("---------- FIN CONVERSION PDF----------", Level.INFO);
                                }else{
                                    pdfContentTosend = "not allowed";
                                    logger.log("Conversion not allowed for this extension : " + fileExtension, Level.INFO);
                                }
                            }

                            trayIcon.displayMessage("Maarch content editor", "Envoi du document ...", TrayIcon.MessageType.INFO);
                            String urlToSave = url + "?action=saveObject&objectType=" + objectType
                                    + "&objectTable=" + objectTable + "&objectId=" + objectId
                                    + "&uniqueId=" + uniqueId + "&idApplet=" + idApplet + "&step=end&userMaarch=" + userMaarch
                                    + "&onlyConvert=" + onlyConvert;
                            logger.log("----------BEGIN SEND OF THE OBJECT----------", Level.INFO);
                            logger.log("URL TO SAVE : " + urlToSave, Level.INFO);
                            sendHttpRequest(urlToSave, fileContentTosend, true);
                            logger.log("MESSAGE STATUS : " + messageStatus, Level.INFO);
                            logger.log("LAST MESSAGE RESULT : ", Level.INFO);
                            processReturn(messageResult);

                            if ("true".equals(convertPdf)) {
                                if (pdfContentTosend == "null") {
                                    endMessage = endMessage + " mais la conversion pdf n'a pas fonctionné (le document ne pourra pas être signé)";
                                }
                            }
                            File fileToRename = new File(userLocalDirTmp + File.separator + fileToEdit);
                            String newMd5 = getchecksumFile(userLocalDirTmp + File.separator + fileToEdit);
                            fileToRename.renameTo(new File(userLocalDirTmp + File.separator + newMd5 + "." + fileExtension));
                            FileManager.deleteSpecificFilesOnDir(fileToDelete);
                            FileManager.deleteEnvDir(userLocalDirTmp + File.separator + idApplet + "_conv");
                            FileManager.deleteFilesOnDirWithTime(userLocalDirTmp);
                            logger.log("----------END SEND OF THE OBJECT----------", Level.INFO);
                            return;
                        }
                    }
                }
                 
                boolean valid = key.reset();
                if (!valid) {
                    break;
                }
            }
             
        } catch (IOException ex) {
            System.err.println(ex);
        }
    }
    
    public void launchOnlyConvert() throws UnsupportedEncodingException, InterruptedException, IOException, PrivilegedActionException, Exception {

        String urlToSend = url + "?action=editObject&objectType=" + objectType
            + "&objectTable=" + objectTable + "&objectId=" + objectId
            + "&uniqueId=" + uniqueId;
        
        logger.log("ONLYCONVERT GET DOCUMENT : " + urlToSend, Level.INFO);
        
        sendHttpRequest(urlToSend, "none", false);
        processReturn(messageResult);
        
        logger.log("MESSAGE STATUS : " + messageStatus, Level.INFO);
        logger.log("MESSAGE RESULT : ", Level.INFO);
        
        fileToEdit = "thefile_" + idApplet + "." + fileExtension;
        
        logger.log("CREATE FILE IN LOCAL PATH :" + fileToEdit, Level.INFO);
        
        fM.createFile(fileContent, userLocalDirTmp + File.separator + fileToEdit);
        fileToDelete.add(userLocalDirTmp + File.separator + fileToEdit);
        fileContentTosend = FileManager.encodeFile(userLocalDirTmp + File.separator + fileToEdit);
        if ((fileExtension.equalsIgnoreCase("docx") || fileExtension.equalsIgnoreCase("doc") || fileExtension.equalsIgnoreCase("docm") || fileExtension.equalsIgnoreCase("odt") || fileExtension.equalsIgnoreCase("ott"))) {
            logger.log("----------CONVERSION PDF----------", Level.INFO);
            String pdfFile = userLocalDirTmp + File.separator + "thefile_" + idApplet + ".pdf";
            createPDF(userLocalDirTmp + File.separator + fileToEdit, userLocalDirTmp, os);
            File file=new File(pdfFile);
            if (file.exists()) {
                pdfContentTosend = FileManager.encodeFile(pdfFile);
                fileToDelete.add(pdfFile);

            } else {
                pdfContentTosend = "null";
                logger.log("ERREUR DE CONVERSION PDF !", Level.WARNING); 
            }

            logger.log("---------- FIN CONVERSION PDF----------", Level.INFO);
        }else{
            pdfContentTosend = "not allowed";
            logger.log("Conversion not allowed for this extension : " + fileExtension, Level.INFO);    
        }
        trayIcon.displayMessage("Maarch content editor", "Envoi du document ...", TrayIcon.MessageType.INFO);
        String urlToSave = url + "?action=saveObject&objectType=" + objectType
                + "&objectTable=" + objectTable + "&objectId=" + objectId
                + "&uniqueId=" + uniqueId + "&idApplet=" + idApplet + "&step=end&userMaarch=" + userMaarch
                + "&onlyConvert=" + onlyConvert;
        logger.log("----------BEGIN SEND OF THE OBJECT----------", Level.INFO);
        logger.log("URL TO SAVE : " + urlToSave, Level.INFO);
        sendHttpRequest(urlToSave, fileContentTosend, true);
        logger.log("MESSAGE STATUS : " + messageStatus, Level.INFO);
        logger.log("LAST MESSAGE RESULT : ", Level.INFO);
        processReturn(messageResult);

        FileManager.deleteSpecificFilesOnDir(fileToDelete);
        FileManager.deleteEnvDir(userLocalDirTmp + File.separator + idApplet + "_conv");
        logger.log("----------END SEND OF THE OBJECT----------", Level.INFO);
        return;
    }
    
    public void endRequestApplet() throws UnsupportedEncodingException, InterruptedException {
        fileContentTosend = "";
        String urlToSave = url + "?action=terminate&objectType=" + objectType
            + "&objectTable=" + objectTable + "&objectId=" + objectId
            + "&uniqueId=" + uniqueId + "&idApplet=" + idApplet + "&step=end&userMaarch=" + userMaarch
            + "&onlyConvert=" + onlyConvert;
        logger.log("REQUEST END APPLET : " + urlToSave, Level.INFO);
        sendHttpRequest(urlToSave, "none", true);
        logger.log("MESSAGE STATUS : " + messageStatus, Level.INFO);
        logger.log("LAST MESSAGE RESULT : ", Level.INFO);
        return;
    }
    
    public Boolean checksumFile(String md5) throws NoSuchAlgorithmException, FileNotFoundException, IOException {
        if (md5.equals("0")) {
            return false;
        }
        MessageDigest md = MessageDigest.getInstance("MD5");
        FileInputStream fis = null;
        File dir = new File(userLocalDirTmp);
        File[] directoryListing = dir.listFiles();
        if (directoryListing != null) {
          for (File child : directoryListing) {
            if (child.toString().contains(md5)) {
                
                String checksum = getchecksumFile(child.toString());
                System.out.println("MD5 checksum file found ! " + checksum);
                if (checksum.equals(md5)) {
                    fileToEdit = child.getName().toString();
                    return true;
                } else {
                    return false;
                }
            }
          }
        }
        return false;
    }
    
    public String getchecksumFile(String fileName) throws NoSuchAlgorithmException, FileNotFoundException, IOException {

        MessageDigest md = MessageDigest.getInstance("MD5");
        
        FileInputStream fis = new FileInputStream(fileName);

        byte[] dataBytes = new byte[1024];

        int nread = 0; 
        while ((nread = fis.read(dataBytes)) != -1) {
          md.update(dataBytes, 0, nread);
        };
        byte[] mdbytes = md.digest();

        //convert the byte to hex format method 1
        StringBuffer sb = new StringBuffer();
        for (int i = 0; i < mdbytes.length; i++) {
          sb.append(Integer.toString((mdbytes[i] & 0xff) + 0x100, 16).substring(1));
        }
        System.out.println("MD5 checksum file : " + sb.toString());
        return sb.toString();
    }
} 