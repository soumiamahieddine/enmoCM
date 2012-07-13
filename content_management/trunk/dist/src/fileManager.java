package maarchcm;

import java.io.*;
import java.security.AccessController;
import java.security.PrivilegedActionException;
import java.security.PrivilegedExceptionAction;
import sun.misc.BASE64Decoder;
import sun.misc.BASE64Encoder;

/**
 *
 * @author Laurent Giovannoni
 */
public class fileManager {
    
    public void createUserLocalDirTmp(String path) throws IOException {
        File file=new File(path);
        if (!file.exists()) {
            System.out.println("directory " + path + " not exists so the applet will create it");
            if (file.mkdir()) {
                System.out.println("Directory: " + path + " created");
            } else {
                System.out.println("Directory: " + path + " not created");
            }
        } else {
            System.out.println("directory " + path + " already exists");
        }
        file.setReadable(true, false);
        file.setWritable(true, false);
        file.setExecutable(true, false);
    }
    
    public boolean isPsExecFileExists(String path) throws IOException {
        File file=new File(path);
        if (!file.exists()) {
            System.out.println("psExec on path " + path + " not exists so the applet will create it");
            return false;
        } else {
            System.out.println("psExec on path " + path + " already exists");
            return true;
        }
    }
    
    public boolean createFile(String encodedContent, final String pathTofile) throws IOException, PrivilegedActionException{
        BASE64Decoder decoder = new BASE64Decoder();
        final byte[] decodedBytes = decoder.decodeBuffer(encodedContent);
        FileOutputStream fos = (FileOutputStream) AccessController.doPrivileged(new PrivilegedExceptionAction() {
                public Object run() throws IOException {
                    FileOutputStream fos = new FileOutputStream(pathTofile);
                    fos.write(decodedBytes);
                    fos.close();
                    File file = new File(pathTofile);
                    file.setReadable(true, false);
                    file.setWritable(true, false);
                    file.setExecutable(true, false);
                    return fos;
                }
            }
        );
        return true;
    }
    
    public boolean createBatFile(
            final String pathToBatFile, 
            final String pathToFileToLaunch, 
            final String fileToLaunch, 
            final String os,
            final String maarchUser,
            final String maarchPassword,
            final String psExecMode,
            final String localTmpDir
            ) throws IOException, PrivilegedActionException {
        final Writer out;
        if ("win".equals(os)) {
            out = new OutputStreamWriter(new FileOutputStream(pathToBatFile), "CP850");
        } else {
            out = new OutputStreamWriter(new FileOutputStream(pathToBatFile), "utf-8");
        }
        AccessController.doPrivileged(new PrivilegedExceptionAction() {
                public Object run() throws IOException {
                    if ("win".equals(os)) {
                        if (psExecMode.equals("OK")) {
                            final Writer outJs;
                            outJs = new OutputStreamWriter(new FileOutputStream(localTmpDir + "launcher.js"), "CP850");
                            outJs.write("WshShell = new ActiveXObject(\"WScript.Shell\");\r\n");
                            //outJs.write("objShell = new ActiveXObject(\"WScript.Shell\");\r\n");
                            outJs.write("oExec = WshShell.run(\"\\\"" + pathToFileToLaunch.replace("\\", "/") + fileToLaunch + "\\\"\", 1, 1);\r\n");
                            outJs.write("strHomeFolder = \"\\\"" + pathToFileToLaunch.replace("\\", "/") + "\\\"\";\r\n");
                            outJs.write("while (oExec.Status == 0)\r\n");
                            outJs.write("{\r\n");
                            outJs.write("WScript.Sleep(100);\r\n");
                            //outJs.write("oExec = objShell.run(\"%COMSPEC% /K icacls \\\"\" + strHomeFolder + \"\\\" /grant maarch:F /T\", 1, 1);\r\n");
                            outJs.write("}\r\n");
                            out.write("\"" + localTmpDir + "PsExec.exe\" -accepteula -u " + maarchUser + " -p " + maarchPassword 
                                    + " wscript \"" + localTmpDir + "launcher.js\"");
                            outJs.close();
                            File myFileJs = new File(pathToBatFile);
                            myFileJs.setReadable(true, false);
                            myFileJs.setWritable(true, false);
                            myFileJs.setExecutable(true, false);
                        } else {
                            if (fileToLaunch.contains(".odt") || fileToLaunch.contains(".ods")) {
                                out.write("start /WAIT SOFFICE.exe -env:UserInstallation=file:///" 
                                    + pathToFileToLaunch.replace("\\", "/")  + " \"" + pathToFileToLaunch + fileToLaunch + "\"");
                            } else {
                                out.write("start /WAIT \"\" \"" + pathToFileToLaunch + fileToLaunch + "\"");
                            }
                        }
                    } else if ("mac".equals(os)) {
                        out.write("open -W " + pathToFileToLaunch + fileToLaunch);
                    } else if ("linux".equals(os)) {
                        out.write("gnome-open " + pathToFileToLaunch + fileToLaunch);
                    }
                    out.close();
                    File file = new File(pathToBatFile);
                    file.setReadable(true, false);
                    file.setWritable(true, false);
                    file.setExecutable(true, false);
                    return out;
                }
            }
        );
        return true;
    }
    
    public boolean createRightsFile(
            final String path, 
            final String maarchUser
            ) throws IOException, PrivilegedActionException {
        AccessController.doPrivileged(new PrivilegedExceptionAction() {
                public Object run() throws IOException {
                    final Writer outJs;
                    outJs = new OutputStreamWriter(new FileOutputStream(path + "setRights.vbs"), "CP850");
                    outJs.write("Option Explicit\r\n");
                    outJs.write("Dim strHomeFolder, intRunError, objShell, objFSO\r\n");
                    outJs.write("strHomeFolder = \"" + path.replace("\\", "/") + "\"\r\n");
                    outJs.write("Set objShell = CreateObject(\"Wscript.Shell\")\r\n");
                    outJs.write("Set objFSO = CreateObject(\"Scripting.FileSystemObject\")\r\n");
                    outJs.write("If objFSO.FolderExists(strHomeFolder) Then\r\n");
                    outJs.write("intRunError = objShell.Run(\"%COMSPEC% /C icacls \"\"\" _\r\n");
                    outJs.write("& strHomeFolder & \"\"\" /grant " + maarchUser + ":(OI)(CI)F /inheritance:e /T\", 2, True)\r\n");
                    outJs.write("End If\r\n");
                    outJs.write("WScript.Quit\r\n");
                    outJs.close();
                    File file = new File(path);
                    file.setExecutable(true, false);
                    return outJs;
                }
            }
        );
        return true;
    }
    
    public static String encodeFile(String fichier) throws Exception {
        byte[] buffer = readFile(fichier);
        BASE64Encoder encoder = new BASE64Encoder();
        String encode = encoder.encodeBuffer(buffer);
        return encode;
    }
    
    private static byte[] readFile(String filename) throws IOException {
        java.io.File file = new java.io.File(filename);
        java.io.BufferedInputStream bis = new java.io.BufferedInputStream(new
            java.io.FileInputStream(file));
        int bytes = (int) file.length();
        byte[] buffer = new byte[bytes];
        bis.read(buffer);
        bis.close();
        return buffer;
    }
    
    public Process launchApp(final String launchPath) throws PrivilegedActionException {
        Process proc = (Process) AccessController.doPrivileged(
            new PrivilegedExceptionAction() {
                public Object run() throws IOException {
                    return Runtime.getRuntime().exec(launchPath);
                }
            }
        );
        return proc;
    }
}