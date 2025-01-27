import sys
import xml.etree.ElementTree as ET

def update_update_xml(version, download_url, sha512):
    # Load the XML file
    tree = ET.parse('update.xml')
    root = tree.getroot()

    # Update the version
    version_element = root.find('.//update/version')
    if version_element is not None:
        version_element.text = version

    # Update the download URL
    download_url_element = root.find('.//update/downloads/downloadurl')
    if download_url_element is not None:
        download_url_element.text = download_url

    # Update the SHA512 checksum
    sha512_element = root.find('.//update/sha512')
    if sha512_element is not None:
        sha512_element.text = sha512

    # Save the updated XML
    tree.write('update.xml')

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: update_update_xml.py <version> <download_url> <sha512>")
        sys.exit(1)

    version, download_url, sha512 = sys.argv[1], sys.argv[2], sys.argv[3]
    update_update_xml(version, download_url, sha512)
