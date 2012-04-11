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
    
    public boolean createFile(String encodedContent, final String pathTofile) throws IOException, PrivilegedActionException{
        BASE64Decoder decoder = new BASE64Decoder();
        final byte[] decodedBytes = decoder.decodeBuffer(encodedContent);
        FileOutputStream fos = (FileOutputStream) AccessController.doPrivileged(new PrivilegedExceptionAction() {
                public Object run() throws IOException {
                    FileOutputStream fos = new FileOutputStream(pathTofile);
                    fos.write(decodedBytes);
                    fos.close();
                    return fos;
                }
            }
        );
        return true;
    }
    
    public boolean createBatFile(final String pathToBatFile, final String fileToLaunch) throws IOException, PrivilegedActionException {
        final Writer out = new OutputStreamWriter(new FileOutputStream(pathToBatFile), "utf-8");
        AccessController.doPrivileged(new PrivilegedExceptionAction() {
                public Object run() throws IOException {
                    out.write("start /WAIT " + fileToLaunch);
                    out.close();
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