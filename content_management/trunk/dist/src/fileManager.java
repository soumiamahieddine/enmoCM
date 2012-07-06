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
                    File myFile = new File(pathTofile);
                    myFile.setExecutable(true);
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
                            out.write(localTmpDir + "PsExec.exe -u " + maarchUser + " -p " + maarchPassword 
                                    + " cmd /c start /WAIT /D \"" + pathToFileToLaunch + "\" " + fileToLaunch);
                        } else {
                            if (fileToLaunch.contains(".odt") || fileToLaunch.contains(".ods")) {
                                out.write("start /WAIT SOFFICE.exe -env:UserInstallation=file:///" 
                                    + pathToFileToLaunch.replace("\\", "/")  + " \"" + pathToFileToLaunch + fileToLaunch + "\"");
                            } else {
                                out.write("start /WAIT /D \"" + pathToFileToLaunch + "\" " + fileToLaunch);
                            }
                        }
                    } else if ("mac".equals(os)) {
                        out.write("open -W " + pathToFileToLaunch + fileToLaunch);
                    } else if ("linux".equals(os)) {
                        out.write("gnome-open " + pathToFileToLaunch + fileToLaunch);
                    }
                    out.close();
                    File myFile = new File(pathToBatFile);
                    myFile.setExecutable(true);
                    return out;
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