#!/usr/bin/env python3
import os
import requests
from pathlib import Path
import concurrent.futures
import time

class SoundDownloader:
    def __init__(self):
        self.base_url = "https://ketqua.vn/assets/sounds"
        self.output_dir = Path("public/assets/sounds")

        # List of all sound files to download
        self.sound_files = [
            # Number sounds
            *[f"{i}.mp3" for i in range(10)],

            # Theme and intro sounds
            "xoso-music-theme.mp3",
            "intro-first.mp3",
            "intro-last.mp3",
            "ting.mp3",

            # Prize sounds
            *[f"quay-giai-{i}.mp3" for i in range(1, 8)],
            "quay-giai-db.mp3",

            # Round sounds
            *[f"lan-{i}.mp3" for i in range(1, 7)],

            # Prize round combinations
            *[f"quay-giai-{i}_lan-{j}.mp3" for i in range(1, 8) for j in range(1, 7)]
        ]

    def create_directory(self):
        """Create output directory if it doesn't exist"""
        self.output_dir.mkdir(parents=True, exist_ok=True)
        print(f"Created directory: {self.output_dir}")

    def download_file(self, filename):
        """Download a single sound file"""
        url = f"{self.base_url}/{filename}"
        output_path = self.output_dir / filename

        # Skip if file already exists
        if output_path.exists():
            print(f"Skipping {filename} - already exists")
            return True

        try:
            response = requests.get(url, timeout=10)
            if response.status_code == 200:
                with open(output_path, 'wb') as f:
                    f.write(response.content)
                print(f"Downloaded {filename}")
                return True
            else:
                print(f"Failed to download {filename}: Status {response.status_code}")
                return False
        except Exception as e:
            print(f"Error downloading {filename}: {str(e)}")
            return False

    def download_all(self):
        """Download all sound files using thread pool"""
        self.create_directory()

        print(f"Starting download of {len(self.sound_files)} files...")
        start_time = time.time()

        # Use thread pool for parallel downloads
        with concurrent.futures.ThreadPoolExecutor(max_workers=5) as executor:
            results = list(executor.map(self.download_file, self.sound_files))

        # Print summary
        success_count = sum(1 for r in results if r)
        print(f"\nDownload complete!")
        print(f"Successfully downloaded: {success_count}/{len(self.sound_files)} files")
        print(f"Time taken: {time.time() - start_time:.2f} seconds")

if __name__ == "__main__":
    downloader = SoundDownloader()
    downloader.download_all()
